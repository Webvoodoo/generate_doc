<?php
ob_start();//активируем промежуточный кэш
header("Cache-Control: no-store, no-cache");
if($_COOKIE["count"] == false){setcookie("count", 1);}
?>
<?php
session_start();//Активируем сессии
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\TemplateProcessor;
require "vendor/autoload.php";
/////*****************************************************begin admin settings****************************************************/////
//menu icon for plugin in admin panel
function webvoodoo_gen_doc_admin_menu(){
	add_menu_page( 'Настроки генератора документов',
					'Генератор документов',
					'administrator',
					'settings_gen_doc',
					'admin_generate_doc_page');
}
//plugin page with settings for administrator
function admin_generate_doc_page() {
    echo "<h1>Настройки генератора документов</h1><br/>";?>
    <p>Чтобы подключить плагин на страницу нужно:</p>
    <ol>
        <li>Создать отдельную страницу (если таковой нет), на которой будет располагаться форма для генерации документов.</li>
        <li>Если страница есть, то в поле ниже указать название данной страницы и нажать Сохранить.</li>
    </ol>
    <?php if (is_admin()):
        global $wpdb;
        $page_setup = $wpdb->prefix."webvoodoo_page_name_setup";
		if($wpdb->get_var( "SHOW TABLES LIKE '$page_setup'" ) == $page_setup) {
        	$get_name = $wpdb->query("SELECT * FROM `" . $page_setup . "`;");
		}        
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
	        if(!post_exists(wp_slash(strip_tags($_POST['webvoodoo_name_page'])))) {
	            wp_die("Страница не существует, создайте ее.");
            } else {
                if(function_exists('check_admin_referrer')) {
                    check_admin_referer('webvoodoo_admin_form');
                }
                if($get_name == 0) {
                    $wpdb->query($wpdb->prepare("INSERT INTO `$page_setup` (`id`, `name`) VALUES(%s , %s)", 'webvoodoo_page_name_adm', trim(strip_tags($_POST['webvoodoo_name_page']))));
                } else {
                    $wpdb->query($wpdb->prepare("UPDATE `$page_setup` SET `name` = %s WHERE `id` = 'webvoodoo_page_name_adm';" , trim(strip_tags($_POST['webvoodoo_name_page']))));
                }
                $get_name = $wpdb->query("SELECT COUNT(`name`) FROM `" . $page_setup . "`;");                
            }
        } ?>
        <form action="<?php echo $_SERVER['PHP_SELF'] . "?page=settings_gen_doc&amp;updated=true" ?>" method="POST" name="webvoodoo_admin_form">
            <?php if(function_exists('wp_nonce_field')){
                wp_nonce_field('webvoodoo_admin_form');
            }
            $page_setup = $wpdb->prefix."webvoodoo_page_name_setup";
			if($wpdb->get_var( "SHOW TABLES LIKE '$page_setup'" ) == $page_setup) {
            	$get_name = $wpdb->query("SELECT COUNT(`name`) FROM `" . $page_setup . "`;");
			}
            if($get_name == 1) {
	            $get_str = $wpdb->get_var("SELECT `name` FROM `" . $page_setup . "` WHERE `id` = 'webvoodoo_page_name_adm';"); ?>
            <label>Название страницы: <b><?php echo $get_str . "</b></label><br/>"; } else {?>
            <?php echo "<label>Название страницы: </label><br/>"; } ?>
            <input type="text" name="webvoodoo_name_page" id="webvoodoo_page_setting" /><br/>
            <input name="webvoodoo_submit" type="submit" class="button-primary" value="Сохранить" />
        </form>
        <?php $table_name = $wpdb->prefix."webvoodoo_generate_docs";
		$db_data = $wpdb->get_results("SELECT * FROM `$table_name`");
		echo "<hr><h2>Данные из формы</h2><br>";
		if(count($db_data) != 0){
			echo "<table border='1'><thead><tr><th>Id</th>
                <th>Вид документа</th>
                <th>Орган</th><th>Коммент 1</th>
                <th>Место</th><th>Коммент 2</th>
                <th>Дата и время</th><th>Коммент 3</th>
                <th>Цель</th><th>Коммент 4</th>
                <th>Число участников</th><th>Коммент 5</th>
                <th>Будет что-то</th><th>Коммент 6</th>
                <th>ФИО</th><th>Коммент 7</th>
                <th>Дата</th><th>Коммент 8</th></tr></thead><tbody>";
			foreach ($db_data as $data) {
				echo "<tr>";
				foreach ($data as $d) {
					echo "<td>". $d . "</td>";
				} echo "</tr>";
			} echo "</tbody>";
        } endif;
}
//add dashboard widget on main page
function webvoodoo_dashboard_doc_widget() {
    $user = wp_get_current_user();
    if(is_super_admin($user->ID)) {
        wp_add_dashboard_widget( 'webvoodoo_dashboard_doc_widget', 'Онлайн генератор документов', 'webvoodoo_dashboard_doc_widget_show' );
    }
}
function webvoodoo_dashboard_doc_widget_show(){
    $user = wp_get_current_user();
    if(is_super_admin($user->ID)) {
        $files = glob('../wp-content/plugins/webvoodoo_gen_doc/downloads_documents/*.docx');
        echo "<p>Сгенерировано документов: <span id='webvoodoo_count'>" . count($files) . "</span></p><br>";
        if(count($files) != 0) echo "<input type='button' class='button button-primary' name='webvoodoo_widget_del_docs' id='webvoodoo_widget_del_docs' value='Очистить папку с документами' />";
    }
}
function wp_ajax_webvoodoo_del_docs() {
    $files = glob('../wp-content/plugins/webvoodoo_gen_doc/downloads_documents/*.docx');
    foreach($files as $file){
        unlink($file);
    }
    wp_die("Все документы удалены");
}
//connect the admin scripts and styles
function webvoodoo_gen_doc_admin_js_and_css_connect() {
	wp_enqueue_script("webvoodoo_script_admin", plugins_url("/js/webvoodoo_script_admin.js", __FILE__),array('jquery'),null,true);
	wp_enqueue_style("webvodoo_style_admin", plugins_url("/css/webvodoo_style_admin.css", __FILE__));
	wp_localize_script("webvoodoo_script_admin",	"webvoodooDoc",	['nonce' => wp_create_nonce('webvoodooDoc'),	]);
}/////******************************************************end admin settings****************************************************/////
/////*****************************************************begin user settings****************************************************/////
//show the plugin content on page
function webvoodoo_gen_doc($content) {
	global $wpdb;
	$page_setup = $wpdb->prefix."webvoodoo_page_name_setup";
	$key = $wpdb->get_row("SELECT `id`, `name` FROM `" . $page_setup . "` ;");
	if($key->id == "webvoodoo_page_name_adm") {
		if(!is_page($key->name)) return $content;
		else { ?>
            <div class="row" id='webvoodoo_doc_content'>
                <div class="col-md-4 webvoodoo_col_left">
                    <form id="webvoodoo_form_1" method="POST" action="<?php echo $_SERVER['REQUEST_URI'];?>">
                        <button type="submit" class="btn btn-info" id="webvoodoo_get_form">Отправить</button><br/><br/>
                        <label>Вид документа:</label>
                        <select id='webvoodoo_select_doc' name="doc_type">
                            <option value='Трудовой договор'>Трудовой договор</option>
                            <option value='Должностная инструкция'>Должностная инструкция</option>
                        </select><br>
                        <label>Орган, в который подается:</label><br/>
                        <input  type='text' name="agency" id="agency" value='<?= (isset($_POST['agency'])) ? strip_tags($_POST['agency']) : '' ?>' required/><br/>
                        <label>Комментарий:</label><br/>
                        <textarea id="comment1" name="comment1"></textarea><br/>
                        <label>Место:</label><br/>
                        <input id="place" type='text' name="place" value='<?= (isset($_POST['place'])) ? strip_tags($_POST['place']) : '' ?>' required/><br/>
                        <label>Комментарий:</label><br/>
                        <textarea  id="comment2" name="comment2"></textarea><br/>
                        <label>Дата и время проведения:</label><br/>
                        <input  id="date_and_time" type='text' name="date_and_time" value='<?= (isset($_POST['date_and_time'])) ? strip_tags($_POST['date_and_time']) : '' ?>' required/><br/>
                        <label>Комментарий:</label><br/>
                        <textarea id="comment3" name="comment3"></textarea><br/>
                        <label>Цель:</label><br/>
                        <input  id="target" type='text' name="target" value='<?= (isset($_POST['target'])) ? strip_tags($_POST['target']) : '' ?>' required/><br/>
                        <label>Комментарий:</label><br/>
                        <textarea id="comment4"  name="comment4"></textarea><br/>
                        <label>Предполагаемое количество участников:</label><br/>
                        <input id="participant" type='number' name="participant" min="0" value='<?= (isset($_POST['participant'])) ? strip_tags($_POST['participant']) : '' ?>' required/><br/>
                        <label>Комментарий:</label><br/>
                        <textarea id="comment5" name="comment5"></textarea><br/>
                        <label>Будет или не будет что-то:</label><br/>
                        <input id="yes_or_no" type='radio' name='yes_or_no' value='Да' required/> Да
                        <input id="yes_or_no" type='radio' name='yes_or_no' value='Нет' required/> Нет<br/>
                        <label>Комментарий:</label><br/>
                        <textarea id="comment6" name="comment6"></textarea><br/>
                        <label>Фамилия, имя, отчество:</label><br/>
                        <input id="fio" type='text' name="fio" value='<?= (isset($_POST['fio'])) ? strip_tags($_POST['fio']) : '' ?>' required/><br/>
                        <label>Комментарий:</label><br/>
                        <textarea id="comment7" name="comment7"></textarea><br/>
                        <label>Дата подачи:</label><br/>
                        <input id="date" type='text'name="date" value='<?= (isset($_POST['date'])) ? strip_tags($_POST['date']) : '' ?>' required/><br/>
                        <label>Комментарий:</label><br/>
                        <textarea id="comment8" name="comment8"></textarea><br/>
                    </form>
                </div>
                <div class="col-md-8 webvoodoo_col_right">
                    <?php if($_SERVER['REQUEST_METHOD'] == 'POST') {
                        $get_link = do_action( "webvoodoo_gen_doc_send" );
                        return $content . $get_link; } ?>
                    <!-- Если сессия существует вызываем  webvoodoo_gen_doc_send-->
                    <?php if($_SESSION['doc_type']) {
                        $get_link = do_action( "webvoodoo_gen_doc_send" );
                        return $content . $get_link; } ?>
                    <div id="change_doc_view" >
                        <?php include( "documents/example.php" );?>
                    </div>
                </div>
            </div>
			<?php return $content;}}}
//connect scripts and styles files for user settings
function webvoodoo_gen_doc_js_and_css_connect() {
    global $wpdb;
    $page_setup = $wpdb->prefix."webvoodoo_page_name_setup";
	if($wpdb->get_var( "SHOW TABLES LIKE '$page_setup'" ) == $page_setup) {
		$key = $wpdb->get_row("SELECT `id`, `name` FROM `" . $page_setup . "` ;");
		if($key->id == "webvoodoo_page_name_adm") {
			if(is_page($key->name)) {
				wp_enqueue_script("webvoodoo_script", plugins_url("/js/webvoodoo_script.js", __FILE__), array('jquery'), null, true);
				wp_enqueue_style("webvoodoo_style", plugins_url("/css/webvoodoo_style.css", __FILE__));
			}
		}
	}
}
$_COOKIE["count"] += 1;
if($_COOKIE["count"] > 1){session_unset(); setcookie("count", 0);}