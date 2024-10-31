<?php
    /*
     Plugin Name: nxtAuth
     Description: Authentification in Wordpress by NXT token
     Version: 0.6.1
     Author: Alexander (scor2k)
     Email: scor2k@gmail.com
     Author URI: http://nxtauth.tk
     Donate NXT: NXT-FRNZ-PDJF-2CQT-DQ4WQ
     Update: 2014-10-29
     */

     /* Copyright 2014 Alexander (scor2k) ( email: scor2k@gmail.com ) */

    if ( !class_exists('apiNXT') ) {
    class apiNXT {
        protected $hosts = array(); // ip:port
        protected $genesis;
        protected $postfix = '/nxt';
        public $lasthost;

        public function __construct($hosts) {
            $this->genesis = strtotime('24.11.2013 12:00:00 UTC');
            // check is_array 
            if ( ! is_array($hosts) ) {
                $hosts[0] = $hosts;
            }
            $this->hosts = $hosts;

            for ( $i=0; $i<count($this->hosts); $i++ ){
                $t = $this->hosts[$i];
                if ( $this->isAlive($t) ) {
                    $this->lasthost = 'http://'.$t.$this->postfix;
                    break;
                }
            }
        }

        private function sendRequest($url, $params, $timeout = '1') {
            $res = @file_get_contents($url , false, stream_context_create( array(
                'http' => array( 
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content' => http_build_query($params),
                    'timeout' => $timeout
                )
            )));
            return $res;
        }

        private function isAlive($host) {
            $url = 'http://'.$host.$this->postfix;

            $params = array(
                'requestType' => 'getState'
            );

            $res = $this->sendRequest($url, $params); //get Node State
            if ( ! $res ) { return false; } else { $res = json_decode($res, true); }

            $lastblock = isset($res['lastBlock']) ? $res['lastBlock'] : 0; // last block ID
            $time = isset($res['time']) ? $res['time'] : 0; // node time

            if ( $time == 0 || $lastblock == 0 ) { return false; }

            // get block info

            $params = array(
                'requestType' => 'getBlock',
                'block' => $lastblock,
            );

            $res = $this->sendRequest($url, $params);  // get Block Info
            if ( ! $res ) { return false; } else { $res = json_decode($res, true); }
            $blocktime = isset($res['timestamp']) ? $res['timestamp'] : 0;  //block time

            // check diff : block time & node time

            $diff = $time - $blocktime; 
            if ( $diff > 1800 ) { return false; } // 30 min from last block!

            return true;
        }

        public function checkToken($token, $website = '') {
            $website = strlen($website) == 0 ? $_SERVER['SERVER_NAME'] : $website;

             $params = array(
                'requestType' => 'decodeToken',
                'website' => $website,
                'token' => $token
            );

            $res = $this->sendRequest($this->lasthost, $params); // decode token
            if ( ! $res ) { return false; } else { $res = json_decode($res, true); }

            if ( isset($res['accountRS']) && isset($res['valid']) && $res['valid'] == '1' ) {
                $acc = $res['accountRS'];
                return $acc; //return account in Reed Solomon 
            } else { return false; }
        }


     
    }
    } // end if class_exists


    function checkNXTtoken($user, $username, $password) {
       if ( is_a($user, 'WP_User') ) { return $user; }
       
       if ( !isset($_POST['nxtToken'] ) ) { // If User Don't user NXT Token String - exit
            return;
       }
    
        // Get hosts from options
        $hh = get_option('nxtHH');
        if ( strlen($hh) > 5 and json_decode($hh, false) ) {
            $hosts = json_decode($hh, false);
        } else { 
            $hosts[0] = 'localhost:7876';
        }

        $nxt = new apiNXT($hosts);

        $check =  $nxt->checkToken($_POST['nxtToken']); 

        switch ( $check ) {
            case false:
                return new WP_Error('valid_username', '<strong>ERROR:</strong>NOT valid NXT token.' );
                break;
            // CASE 1
            default:
                if ( username_exists($check) ) {
                    return get_user_by('login', $check);
                } else {
                    wp_create_user( $check, $password, '' );
                    return get_user_by('login', $check);
                }
            // CASE 0
       }
    } 

    function successRedirect() {
        return home_url('/');
    }

    function custom_login() {
        ?>

        <script type="text/javascript">
            function checkToken() {
                var token = prompt("Enter valid NXT token, please:");
                document.getElementById('nxtToken').value = token;
                document.getElementById('wp-submit').click();
            }
        </script>

        <link rel='stylesheet' href='/wp-content/plugins/<?php echo plugin_basename(__DIR__); ?>/style.css' type='text/css' />
        <input type='button' class='button button-primary button-large nxtbtn' id='btnNXT' value='NXT' onClick='checkToken()' title='Login with NXT-Token' />
        <input type='hidden' name='nxtToken' id='nxtToken' value='' />

        <?php
    }

    add_filter( 'login_form', 'custom_login' );
    add_filter( 'login_redirect', 'successRedirect'); // Login page Redirect
    add_filter( 'authenticate', 'checkNXTtoken', 5 , 3); // Auth with NXT token
    /*
     * ADMIN MENU
     */
    function nxtAdminPage() {
        add_options_page('nxtAuth','nxtAuth', 8, 'nxtAuthOptions', 'nxtAuthPageContent');
    }

    function nxtAuthPageContent() {
        /*
         * Options page
         */

        // get nxtHostname option

        if ( isset($_POST['nxtHostname']) && isset($_POST['nxtPort'])  ) {
            // submit button was pressed
            update_option('nxtHostname', preg_replace("/ /","",$_POST['nxtHostname']));
            update_option('nxtPort', $_POST['nxtPort']);
        }

        $nxtH = get_option('nxtHostname');
        $nxtP = get_option('nxtPort');


        echo "<div clas='wrap'>";
        echo "<h2>nxtAuth Settings page</h2>";
        echo "<form method=post>";
        settings_fields('nxtOptionsGroup');
        do_settings_sections('nxtOptionsGroup');
        echo "Enter hostname of NXT nodes separated with comma: <br>";
        echo "<input type=text name=nxtHostname value='".$nxtH."'size=60 /><br>";
        echo "Enter default port of NXT nodes: <br>";
        echo "<input type=text name=nxtPort value='".$nxtP."'size=5 /><br>";
        submit_button();
        echo "</form></div>";

        // show using host
        $hosts = split(',', $nxtH);

        echo "<pre>";
        echo "Using nodes for auth: <br>";
        if ( count($hosts) == 0 ) { echo "No servers available, authorization will not work."; }
        $hh = array();
        for ( $i=0; $i<count($hosts); $i++ ) {
            if ( strlen($hosts[$i]) < 7 ) continue;
            $hh[$i] = $hosts[$i].":".$nxtP;
        }
        $hh = json_encode($hh);
        echo $hh;
        echo "</pre>";
        // save json
        update_option('nxtHH', $hh);
    }

    add_action( 'admin_menu', 'nxtAdminPage' );

?>


