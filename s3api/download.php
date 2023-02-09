<?php 
/**
 * @ Author: Bill Minozzi
 * @ Copyright: 2022 www.BillMinozzi.com
 * Created: 2022 - Sept 20
 */

if (defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Aws\S3\Exception\S3Exception;
class TOOLSFORS3 {
    public function get_file(){
      ini_set('memory_limit', '512M');
      set_time_limit(600);

      if(!function_exists('esc_attr')){
        function esc_attr($text){
          return str_replace('<?php ','',$text);
        }
      }
      if(!function_exists('sanitize_text_field')){
        function sanitize_text_field($text){
          return strip_tags($text);
        }
      }

      if(!isset($_GET['key']))
      die('Fail Autentication (-1)');
      if($_GET['key'] != md5($_COOKIE['PHPSESSID']))
        die('Fail Autentication (-2)');
      require '../vendor/autoload.php';
      $config = array();
      $config_val = array();
      $config[0] = 'bucket';
      $config[1] = 'file';
      $config[2] = 'region';
      $config[3] = 'access_key';
      $config[4] = 'secret_key';
      $config[5] = 'end_points';
      for($i = 0; $i < count($config); $i++){
          if( isset($_GET[$config[$i]])){
              $config_val[$i] = rawurldecode(sanitize_text_field($_GET[$config[$i]]));
          }
          else {
          ob_end_flush();
          die('Missing Parameter: '.$config[$i]);
          }
      }
      define("TOOLSFORS3FILENAME", basename($config_val[1]));
      $config = [
          's3-access' => [
              'key' => $config_val[3],
              'secret' => $config_val[4],
              'bucket' => $config_val[0],
              'region' => $config_val[2],
              'version' => 'latest',
              'endpoint' => $config_val[5]
          ]
      ];
      $toolsfors3_access_key = $config_val[3];
      $s3 = new Aws\S3\S3Client([
          'credentials' => [
              'key' => $config['s3-access']['key'],
              'secret' => $config['s3-access']['secret']
          ],
          'use_path_style_endpoint' => true,
          'force_path_style' => true,
          'endpoint' => $config['s3-access']['endpoint'],
          'version' => 'latest',
          'region' => $config['s3-access']['region']
      ]);
      try {
          $result = $s3->getObject([
              'Bucket' => $config_val[0],
              'Key'    => $config_val[1],
              'ResponseContentDisposition' => 'attachment; filename="'.$config_val[1].'"'
          ]);
          return $result['Body'];
      } catch (S3Exception $e) {
          ob_end_flush();
          error_log(explode(';', $e->getMessage())[1]);
          die('error -1001 '.esc_attr(explode(';', $e->getMessage())[1]));
      }
    }
}
$toolsfors3 = new TOOLSFORS3();
$result = $toolsfors3->get_file();
        ///////// final ///////////////////////
      try {
        if ( headers_sent() ) {
          ob_end_flush();
          die( "File: ". __FILE__ ." Line: ". __LINE__ .  " Cannot dispatch file, headers already sent." );
        }
          if ( gettype($result) !== 'object' ) {
              ob_end_flush();
              die( "File: ". __FILE__ ." Line: ". __LINE__ .  " Cannot dispatch file, Unable to get file from s3 tool." );
          }
          header( 'Content-Description: File Transfer' );
          header( 'Content-Type: application/octet-stream' ); // http://stackoverflow.com/a/20509354
          header('Content-Disposition: attachment; filename="'.esc_attr(TOOLSFORS3FILENAME).'"');
          header( 'Expires: 0' );
          header( 'Cache-Control: must-revalidate' );
          header( 'Pragma: public' );
          header('Content-length: '.strlen($result));
          ob_end_clean();
          echo esc_attr($result);
          exit;
      } catch (S3Exception $e) {
          echo esc_attr($e->getMessage()) . PHP_EOL;
          die();
      }
      if(!function_exists('esc_attr')){
        function esc_attr($text){
          return str_replace('<?php ','',$text);
        }
      }
      if(!function_exists('sanitize_text_field')){
        function sanitize_text_field($text){
          return strip_tags($text);
        }
      }
