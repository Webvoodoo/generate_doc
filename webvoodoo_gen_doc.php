<?php
ob_start();
header("Cache-Control: no-store, no-cache");
?>
<?php
session_start();
/**
 * Plugin Name: Генератор документов
 * Description: Плагин позволяет сгенерировать онлайн документ с данными от пользователя, и скачать его в формате .docx
 * Plugin URI:  https://seolab.dp.ua
 * Author URI:  https://seolab.dp.ua
 * Author:      Webvoodoo
 * Version:     1.0
 */

use PhpOffice\PhpWord\PhpWord;
require __DIR__ . "/functions.php"; //__DIR__ the magic constant. She is showing the folder, where the file is situate.
require "vendor/autoload.php";
/////user hooks
add_filter("the_content", "webvoodoo_gen_doc"); 
//register scripts and styles only for page where plugin is
add_action("wp_enqueue_scripts", "webvoodoo_gen_doc_js_and_css_connect");
//отработка phpword
add_action("webvoodoo_gen_doc_send", "webvoodoo_gen_doc_send");
///admin hooks
/// //dashboard widget
add_action("wp_dashboard_setup", "webvoodoo_dashboard_doc_widget");
add_action('wp_ajax_webvoodoo_del_docs', 'wp_ajax_webvoodoo_del_docs');
// connect admin js and css files
add_action("admin_enqueue_scripts", "webvoodoo_gen_doc_admin_js_and_css_connect");
add_action( 'admin_menu', 'webvoodoo_gen_doc_admin_menu');
function webvoodoo_gen_doc_send() {
    global $wpdb;
    $phpWord = new PhpWord();
    if($_SESSION['doc_type'] == false){
        //Переносим данные из массива пост в сессию
        $_SESSION['doc_type'] = strip_tags ($_POST['doc_type']);
        $_SESSION['agency'] = strip_tags ($_POST['agency']);
        $_SESSION['comment1'] = strip_tags ($_POST['comment1']);
        $_SESSION['place'] = strip_tags ($_POST['place']);
        $_SESSION['comment2'] = strip_tags ($_POST['comment2']);
        $_SESSION['date_and_time'] = strip_tags ($_POST['date_and_time']);
        $_SESSION['comment3'] = strip_tags ($_POST['comment3']);
        $_SESSION['target'] = strip_tags ($_POST['target']);
        $_SESSION['comment4'] = strip_tags ($_POST['comment4']);
        $_SESSION['participant'] = strip_tags ($_POST['participant']);
        $_SESSION['comment5'] = strip_tags ($_POST['comment5']);
        $_SESSION['yes_or_no'] = strip_tags ($_POST['yes_or_no']);
        $_SESSION['comment6'] = strip_tags ($_POST['comment6']);
        $_SESSION['fio'] = strip_tags ($_POST['fio']);
        $_SESSION['comment7'] = strip_tags ($_POST['comment7']);
        $_SESSION['date'] = strip_tags ($_POST['date']);
        $_SESSION['comment8'] = strip_tags ($_POST['comment8']);
        header('Location:'. $_SERVER['HTTP_REFERER']);
    }
    //Переносим данные из сессии в переменные
    $doc_type = $_SESSION['doc_type'];
    $agency = $_SESSION['agency'];
    $comment1 = $_SESSION['comment1'];
    $place = $_SESSION['place'];
    $comment2 = $_SESSION['comment2'];
    $date_and_time = $_SESSION['date_and_time'];
    $comment3 = $_SESSION['comment3'];
    $target = $_SESSION['target'];
    $comment4 = $_SESSION['comment4'];
    $participant = $_SESSION['participant'];
    $comment5 = $_SESSION['comment5'];
    $yes_or_no = $_SESSION['yes_or_no'];
    $comment6 = $_SESSION['comment6'];
    $fio = $_SESSION['fio'];
    $comment7 = $_SESSION['comment7'];
    $date = $_SESSION['date'];
    $comment8 = $_SESSION['comment8'];

    if($doc_type == 'Трудовой договор') {
        $document = new \PhpOffice\PhpWord\TemplateProcessor(plugins_url("/template/Template.docx", __FILE__));
        $document->setValue('doc_type', $doc_type);
        $document->setValue('agency', $agency);
        $document->setValue('comment1', $comment1);
        $document->setValue('place', $place);
        $document->setValue('comment2', $comment2);
        $document->setValue('date_and_time', $date_and_time);
        $document->setValue('comment3', $comment3);
        $document->setValue('target', $target);
        $document->setValue('comment4', $comment4);
        $document->setValue('participant', $participant);
        $document->setValue('comment5', $comment5);
        $document->setValue('yes_or_no', $yes_or_no);
        $document->setValue('comment6', $comment6);
        $document->setValue('fio', $fio);
        $document->setValue('comment7', $comment7);
        $document->setValue('date', $date);
        $document->setValue('comment8', $comment8);
        $document_name = "Document_" . date("Y-m-d") . "_". $fio . ".docx";
        $document->saveAs("wp-content/plugins/webvoodoo_gen_doc/downloads_documents/". $document_name);
        echo "<a class='button button-primary webvoodoo_download_doc' href='" . plugins_url("/downloads_documents/" . $document_name, __FILE__). "' download role='button'><i class='fa fa-download'></i>СКАЧАТЬ</a>";
    }
    if($doc_type == 'Должностная инструкция') {
        $document = new \PhpOffice\PhpWord\TemplateProcessor(plugins_url("/template/Template2.docx", __FILE__));
        $document->setValue('doc_type', $doc_type);
        $document->setValue('agency', $agency);
        $document->setValue('comment1', $comment1);
        $document->setValue('place', $place);
        $document->setValue('comment2', $comment2);
        $document->setValue('date_and_time', $date_and_time);
        $document->setValue('comment3', $comment3);
        $document->setValue('target', $target);
        $document->setValue('comment4', $comment4);
        $document->setValue('participant', $participant);
        $document->setValue('comment5', $comment5);
        $document->setValue('yes_or_no', $yes_or_no);
        $document->setValue('comment6', $comment6);
        $document->setValue('fio', $fio);
        $document->setValue('comment7', $comment7);
        $document->setValue('date', $date);
        $document->setValue('comment8', $comment8);
        $document_name = "Document_" . date("Y-m-d") . "_". $fio . ".docx";
        $document->saveAs("wp-content/plugins/webvoodoo_gen_doc/downloads_documents/". $document_name);
        echo "<a class='button button-primary webvoodoo_download_doc' href='" .plugins_url("/downloads_documents/" . $document_name, __FILE__). "' download role='button'><i class='fa fa-download'></i>СКАЧАТЬ</a>";
    }
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $wpdb->insert($wpdb->prefix."webvoodoo_generate_docs",
            array('doc_type' => strip_tags ($_POST['doc_type']),
                'agency' => strip_tags ($_POST['agency']),
                'comment1' => strip_tags ($_POST['comment1']),
                'place' => strip_tags ($_POST['place']),
                'comment2' => strip_tags ($_POST['comment2']),
                'date_and_time' => strip_tags ($_POST['date_and_time']),
                'comment3' => strip_tags ($_POST['comment3']),
                'target' => strip_tags ($_POST['target']),
                'comment4' => strip_tags ($_POST['comment4']),
                'participant' => strip_tags ($_POST['participant']),
                'comment5' => strip_tags ($_POST['comment5']),
                'yes_or_no' => strip_tags ($_POST['yes_or_no']),
                'comment6' => strip_tags ($_POST['comment6']),
                'fio' => strip_tags ($_POST['fio']),
                'comment7' => strip_tags ($_POST['comment7']),
                'date' => strip_tags ($_POST['date']),
                'comment8' => strip_tags ($_POST['comment8']),
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s'));

    }
}
register_activation_hook(__FILE__, "webvoodoo_gen_doc_install");
register_deactivation_hook(__FILE__, "webvoodoo_gen_doc_uninstall");
function webvoodoo_gen_doc_install(){
    global $wpdb;
    $table_docs = $wpdb->prefix."webvoodoo_generate_docs";
    if($wpdb->get_var( "SHOW TABLES LIKE '$table_docs'" ) != $table_docs ) {
        $sql = "CREATE TABLE IF NOT EXISTS $table_docs (
				  `id` INT NOT NULL AUTO_INCREMENT, `doc_type` VARCHAR(250) NULL,
				  `agency` VARCHAR(250) NULL,
				  `comment1` VARCHAR(250) NULL,
				  `place` VARCHAR(250) NULL,
				  `comment2` VARCHAR(250) NULL,
				  `date_and_time` VARCHAR(250) NULL,
				  `comment3` VARCHAR(250) NULL,
				  `target` VARCHAR(250) NULL,
				  `comment4` VARCHAR(250) NULL,
				  `participant` VARCHAR(250) NULL,
				  `comment5` VARCHAR(250) NULL,
				  `yes_or_no` VARCHAR(250) NULL,
				  `comment6` VARCHAR(250) NULL,
				  `fio` VARCHAR(250) NULL,
				  `comment7` VARCHAR(250) NULL,
				  `date` VARCHAR(250) NULL,
				  `comment8` VARCHAR(250) NULL,
				  PRIMARY KEY (`id`))
				ENGINE = InnoDB DEFAULT CHARSET=utf8;";
        $wpdb->query($sql);
    }
    $page_setup = $wpdb->prefix."webvoodoo_page_name_setup";
    if($wpdb->get_var( "SHOW TABLES LIKE '$page_setup'" ) != $page_setup ) {
        $sql2 = "CREATE TABLE IF NOT EXISTS $page_setup (			  
				  `id` VARCHAR(250) UNIQUE,
				  `name` VARCHAR(250) NULL,			  
				  PRIMARY KEY (`id`))
				ENGINE = InnoDB DEFAULT CHARSET=utf8;";
        $wpdb->query($sql2);
    }
}
function webvoodoo_gen_doc_uninstall(){
    global $wpdb;
    $table_docs = $wpdb->prefix."webvoodoo_generate_docs";
    $sql = "DROP TABLE `" .$table_docs . "`;";
    $wpdb->query($sql);
    $page_setup = $wpdb->prefix."webvoodoo_page_name_setup";
    $sql2 = "DROP TABLE `" .$page_setup . "`;";
    $wpdb->query($sql2);
    $files = glob('../wp-content/plugins/webvoodoo_gen_doc/downloads_documents/*.docx');
    foreach($files as $file){
        unlink($file);
    }
}