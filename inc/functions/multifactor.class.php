<?php
use OTPHP\TOTP;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

trait MultiFactor {
    private function mfaPrivateSettings($decodedJwt = null) {
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
            $CurrentUser = $this->getUserByUsername($Username,true);
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
    }

    public function mfaSettings() {
        $Settings = $this->mfaPrivateSettings();
        unset($Settings['totp_secret']);
        return $Settings;
    }

    public function totpNewRegistration() {
        // Generate a Secret Key
        $totp = TOTP::create();
        $secret = $totp->getSecret();
        
        // Store the Secret Key
        $this->totpStoreSecret($this->mfaPrivateSettings()['id'], $secret);
        
        // Generate the provisioning Uri
        $issuer = $this->config->get('Styling','websiteTitle');
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
    }

    public function totpVerifyRegistration($otp) {
        if (!$this->mfaPrivateSettings()['totp_verified']) {
            $verify = $this->totpVerify($otp);
            if ($verify) {
                $stmt = $this->db->prepare('UPDATE users SET totp_verified = TRUE, multifactor_type = "totp", multifactor_enabled = TRUE WHERE id = :id');
                if ($stmt->execute([':id' => $this->mfaPrivateSettings()['id']])) {
                    $this->logging->writeLog('2FA','Successfully verified TOTP registration','info');
                    $this->api->setAPIResponse('Success','Successfully Verified TOTP Registration');
                    return true;
                } else {
                    $this->logging->writeLog('2FA','Failed to update TOTP registration verified state','warning');
                    $this->api->setAPIResponse('Error','Failed to update TOTP registration verified state');
                    return false;
                };
            } else {
                $this->logging->writeLog('2FA','Failed to verify TOTP registration','warning');
                $this->api->setAPIResponse('Error','Failed to verify TOTP registration');
                return false;
            }
        } else {
            $this->logging->writeLog('2FA','TOTP registration already verified','warning');
            $this->api->setAPIResponse('Error','TOTP registration already verified');
            return false;
        }
    }

    public function totpVerifyUser($otp,$jwt) {
        $decodedJwt = $this->CoreJwt->decodeToken($jwt);
        if ($decodedJwt) {
            if ($decodedJwt->mfa == false) {
                if ($this->mfaPrivateSettings($decodedJwt)['totp_verified']) {
                    $verify = $this->totpVerify($otp,$decodedJwt);
                    if ($verify) {
                        $this->CoreJwt->revokeToken($jwt); // Revoke Temporary Token
                        $newJwt = $this->CoreJwt->generateToken($decodedJwt->username, $decodedJwt->firstname, $decodedJwt->surname, $decodedJwt->email, $decodedJwt->groups, $decodedJwt->type); // Issue new token
                        $this->cookie('set','jwt', $newJwt, 30); // Set the jwt cookie
                        $this->logging->writeLog('2FA','TOTP successfully verified','debug');
                        $this->api->setAPIResponse('Success','Successfully Verified TOTP');
                    } else {
                        $this->logging->writeLog('2FA','Failed to verify TOTP','warning');
                        $this->api->setAPIResponse('Error','Failed to verify TOTP');
                    }
                } else {
                    $this->logging->writeLog('2FA','TOTP setup verification is incomplete','warning');
                    $this->api->setAPIResponse('Error','TOTP setup verification is incomplete');
                    return false;
                }
            } else {
                $this->logging->writeLog('2FA','JWT reuse attempted','warning');
                $this->api->setAPIResponse('Error','This request has already been validated');
            }
        } else {
            $this->logging->writeLog('2FA','JWT token invalid','warning');
            $this->api->setAPIResponse('Error','The JWT token is invalid');
        }
    }

    private function totpVerify($otp,$decodedJwt = null) {
        $secret = $this->totpGetSecret($this->mfaPrivateSettings($decodedJwt)['id']); // Retrieve the secret key from storage
        $totp = TOTP::create($secret);
        $verify = $totp->verify($otp);
        return $verify;
    }

    private function totpStoreSecret($id, $secret) {
        $stmt = $this->db->prepare('UPDATE users SET totp_secret = :totp_secret, totp_verified = FALSE, multifactor_type = NULL, multifactor_enabled = FALSE WHERE id = :id');
        if ($stmt->execute([':totp_secret' => $secret, ':id' => $id])) {
            return true;
        } else {
            $this->logging->writeLog('2FA','Failed to store user\'s secret key','warning');
            return false;
        };
    }

    private function totpGetSecret($id) {
        $stmt = $this->db->prepare("SELECT id, username, totp_secret FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (isset($user['totp_secret'])) {
            return $user['totp_secret'];
        }
    }
}