<?php
use OTPHP\TOTP;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

trait MultiFactor {
    private function mfaPrivateSettings($decodedJwt = null) {
        try {
            if ($decodedJwt) {
                $Username = $decodedJwt->username;
                $Authenticated = true;
            } else {
                $CurrentAuth = $this->getAuth();
                $Authenticated = $CurrentAuth['Authenticated'];
                if ($Authenticated) {
                    $Username = $CurrentAuth['Username'];
                }
            }
            if ($Authenticated) {
                $CurrentUser = $this->getUserByUsername($Username, true);
                return array(
                    'id' => $CurrentUser['id'],
                    'multifactor_enabled' => $CurrentUser['multifactor_enabled'] ?? false,
                    'multifactor_type' => $CurrentUser['multifactor_type'] ?? null,
                    'totp_verified' => $CurrentUser['totp_verified'] ?? false,
                    'totp_secret' => $CurrentUser['totp_secret'] ?? null,
                );
            } else {
                return array(
                    'multifactor_enabled' => false,
                );
            }
        } catch (Exception $e) {
            $this->logging->writeLog('MFA', 'Error in mfaPrivateSettings: ' . $e->getMessage(), 'error');
            return array(
                'multifactor_enabled' => false,
            );
        }
    }

    public function mfaSettings() {
        try {
            $Settings = $this->mfaPrivateSettings();
            unset($Settings['totp_secret']);
            return $Settings;
        } catch (Exception $e) {
            $this->logging->writeLog('MFA', 'Error in mfaSettings: ' . $e->getMessage(), 'error');
            return array(
                'multifactor_enabled' => false,
            );
        }
    }

    // ********** //
    // ** TOTP ** //
    // ********** //

    public function totpNewRegistration() {
        try {
            // Generate a Secret Key
            $totp = TOTP::create();
            $secret = $totp->getSecret();
            
            // Store the Secret Key
            if (!$this->totpStoreSecret($this->mfaPrivateSettings()['id'], $secret)) {
                throw new Exception('Failed to store TOTP secret');
            }
            
            // Generate the provisioning Uri
            $issuer = $this->config->get('Styling', 'websiteTitle');
            $CurrentAuth = $this->getAuth();
            $label = $CurrentAuth['Email'] ?? $CurrentAuth['Username']; // User's email
            $totp->setLabel($label); // Set the label
            $totp->setIssuer($issuer); // Set the issuer
            $uri = $totp->getProvisioningUri();
            
            // Use Endroid QR Code to generate the QR code
            $qrCode = new QrCode($uri);
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            
            // Output the QR code as an image
            return $result;
        } catch (Exception $e) {
            $this->logging->writeLog('MFA', 'Error in totpNewRegistration: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    public function totpVerifyRegistration($otp) {
        try {
            if (!$this->mfaPrivateSettings()['totp_verified']) {
                $verify = $this->totpVerify($otp);
                if ($verify) {
                    $stmt = $this->db->prepare('UPDATE users SET totp_verified = TRUE, multifactor_type = "totp", multifactor_enabled = TRUE WHERE id = :id');
                    if ($stmt->execute([':id' => $this->mfaPrivateSettings()['id']])) {
                        $this->logging->writeLog('2FA', 'Successfully verified TOTP registration', 'info');
                        $this->api->setAPIResponse('Success', 'Successfully Verified TOTP Registration');
                        return true;
                    } else {
                        throw new Exception('Failed to update TOTP registration verified state');
                    }
                } else {
                    throw new Exception('Failed to verify TOTP registration');
                }
            } else {
                $this->logging->writeLog('2FA', 'TOTP registration already verified', 'warning');
                $this->api->setAPIResponse('Error', 'TOTP registration already verified');
                return false;
            }
        } catch (Exception $e) {
            $this->logging->writeLog('2FA', 'Error in totpVerifyRegistration: ' . $e->getMessage(), 'error');
            $this->api->setAPIResponse('Error', $e->getMessage());
            return false;
        }
    }

    public function totpVerifyUser($otp, $jwt) {
        try {
            $decodedJwt = $this->CoreJwt->decodeToken($jwt);
            if ($decodedJwt) {
                if ($decodedJwt->mfa == false) {
                    if ($this->mfaPrivateSettings($decodedJwt)['totp_verified']) {
                        $verify = $this->totpVerify($otp, $decodedJwt);
                        if ($verify) {
                            $this->CoreJwt->revokeToken($jwt); // Revoke Temporary Token
                            $newJwt = $this->CoreJwt->generateToken($decodedJwt->username, $decodedJwt->firstname, $decodedJwt->surname, $decodedJwt->email, $decodedJwt->groups, $decodedJwt->type); // Issue new token
                            $this->cookie('set', 'jwt', $newJwt, 30); // Set the jwt cookie
                            $this->logging->writeLog('2FA', 'TOTP successfully verified', 'debug');
                            $this->api->setAPIResponse('Success', 'Successfully Verified TOTP');
                        } else {
                            throw new Exception('Failed to verify TOTP');
                        }
                    } else {
                        throw new Exception('TOTP setup verification is incomplete');
                    }
                } else {
                    $this->logging->writeLog('2FA', 'JWT reuse attempted', 'warning');
                    $this->api->setAPIResponse('Error', 'This request has already been validated');
                }
            } else {
                throw new Exception('JWT token invalid');
            }
        } catch (Exception $e) {
            $this->logging->writeLog('2FA', 'Error in totpVerifyUser: ' . $e->getMessage(), 'error');
            $this->api->setAPIResponse('Error', $e->getMessage());
            return false;
        }
    }

    private function totpVerify($otp, $decodedJwt = null) {
        try {
            $secret = $this->totpGetSecret($this->mfaPrivateSettings($decodedJwt)['id']); // Retrieve the secret key from storage
            $totp = TOTP::create($secret);
            $verify = $totp->verify($otp);
            return $verify;
        } catch (Exception $e) {
            $this->logging->writeLog('2FA', 'Error in totpVerify: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    private function totpStoreSecret($id, $secret) {
        try {
            $stmt = $this->db->prepare('UPDATE users SET totp_secret = :totp_secret, totp_verified = FALSE, multifactor_type = NULL, multifactor_enabled = FALSE WHERE id = :id');
            if ($stmt->execute([':totp_secret' => $secret, ':id' => $id])) {
                return true;
            } else {
                throw new Exception('Failed to store user\'s secret key');
            }
        } catch (Exception $e) {
            $this->logging->writeLog('2FA', 'Error in totpStoreSecret: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    private function totpGetSecret($id) {
        try {
            $stmt = $this->db->prepare("SELECT id, username, totp_secret FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (isset($user['totp_secret'])) {
                return $user['totp_secret'];
            } else {
                throw new Exception('TOTP secret not found');
            }
        } catch (Exception $e) {
            $this->logging->writeLog('2FA', 'Error in totpGetSecret: ' . $e->getMessage(), 'error');
            return null;
        }
    }
}