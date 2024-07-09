<?php
	class WpFastestCacheDatabaseCleanup{

        public static function set_schedule_event(){
            if(!wp_verify_nonce($_POST["nonce"], 'auto-cleanaup')){
                wp_send_json(array("success" => false));
            }

            if(isset($_POST["status"])){
                if($_POST["status"] == "daily" || $_POST["status"] == "weekly"){

                    if(wp_get_schedule("wpfc_db_auto_cleanup") != $_POST["status"]){
                        wp_clear_scheduled_hook("wpfc_db_auto_cleanup");

                        if( time() > strtotime( 'today 5:00' ) ){
                            wp_schedule_event( strtotime('tomorrow 5:00'), $_POST["status"], "wpfc_db_auto_cleanup");
                        }else{
                            wp_schedule_event( strtotime('today 5:00'), $_POST["status"], "wpfc_db_auto_cleanup");
                        }
                    }

                    wp_send_json(array("success" => true, "status" => wp_get_schedule("wpfc_db_auto_cleanup")));
                }else if($_POST["status"] == "off"){
                    wp_clear_scheduled_hook("wpfc_db_auto_cleanup");

                    wp_send_json(array("success" => true));
                }
            }

            wp_send_json(array("success" => false));
        }

		public static function clean($type){
	        global $wpdb;

            $statics = array();

            switch ($type){
            	case "all_warnings":
            		$wpdb->query("DELETE FROM `$wpdb->posts` WHERE post_type = 'revision';");
            		$wpdb->query("DELETE FROM `$wpdb->posts` WHERE post_status = 'trash';");
            		$wpdb->query("DELETE FROM `$wpdb->comments` WHERE comment_approved = 'spam' OR comment_approved = 'trash' ;");
            		$wpdb->query("DELETE FROM `$wpdb->comments` WHERE comment_type = 'trackback' OR comment_type = 'pingback' ;");
            		$wpdb->query("DELETE FROM `$wpdb->options` WHERE option_name LIKE '%\_transient\_%' ;");

            		break;
                case "post_revisions":
                    $wpdb->query("DELETE FROM `$wpdb->posts` WHERE post_type = 'revision';");

                    break;
                case "trashed_contents":
                    $wpdb->query("DELETE FROM `$wpdb->posts` WHERE post_status = 'trash';");

                    break;
                case "trashed_spam_comments":
                    $wpdb->query("DELETE FROM `$wpdb->comments` WHERE comment_approved = 'spam' OR comment_approved = 'trash' ;");

                    break;
                case "trackback_pingback":
                    $wpdb->query("DELETE FROM `$wpdb->comments` WHERE comment_type = 'trackback' OR comment_type = 'pingback' ;");

                    break;
                case "transient_options":
                    $wpdb->query("DELETE FROM `$wpdb->options` WHERE option_name LIKE '%\_transient\_%' ;");

                    break;
            }

            die(json_encode(array("success" => true)));
		}
	}
?>