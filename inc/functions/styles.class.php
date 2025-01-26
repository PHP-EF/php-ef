<?php
trait Style {
    public function buildCustomStyles() {
        $Styling = $this->config->get('Styling');
        $Styles = '';

        // ** SIDEBAR ** //
        $Styles .= $Styling['sidebar']['mainColour'] ? '.sidebar{background-color:'.$Styling['sidebar']['mainColour'].'!important;} ' : '';
        $Styles .= $Styling['sidebar']['textColour'] ? '.sidebar a, .sidebar i, .sidebar span{color:'.$Styling['sidebar']['textColour'].'!important;} ' : '';
        $Styles .= $Styling['sidebar']['activeColour'] ? '.sidebar .nav-links li a.active, .sidebar .nav-links li a.active i, .sidebar .nav-links li .sub-menu a.active, .sidebar .nav-links li .sub-menu a.active span, .sidebar .nav-links li .sub-menu a.active i{color:'.$Styling['sidebar']['activeColour'].'!important;} ' : '';
        $Styles .= $Styling['sidebar']['submenuColour'] ? '.sidebar .nav-links li .sub-menu, .sidebar .nav-links li .sub-menu li, .sidebar .nav-links li .sub-menu .sub-sub-menu li{background-color:'.$Styling['sidebar']['submenuColour'].'!important;} ' : '';
        $Styles .= $Styling['sidebar']['hoverColour'] ? '.sidebar .nav-links li:hover{background-color:'.$Styling['sidebar']['hoverColour'].'!important;} ' : '';
        $Styles .= $Styling['sidebar']['footerColour'] ? '.sidebar .sidebar-footer{background-color:'.$Styling['sidebar']['footerColour'].'!important;} ' : '';

        // ** NAVBAR ** //
        $Styles .= $Styling['navbar']['mainColour'] ? '.nav-bar{background-color:'.$Styling['navbar']['mainColour'].'!important;} ' : '';
        $Styles .= $Styling['navbar']['textColour'] ? '.nav-bar a, .nav-bar i, .nav-bar span, .nav-bar button{color:'.$Styling['navbar']['textColour'].'!important;} ' : '';
        $Styles .= $Styling['navbar']['submenuColour'] ? '.nav-bar .dropdown-content{background-color:'.$Styling['navbar']['submenuColour'].'!important;} ' : '';


        return $Styles;
    }
}