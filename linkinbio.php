/* 
==================================================
LINKVERSE - SIMPLE LINKTREE CLONE
==================================================
SHORTCODES:
[lv_register]
[lv_login]
[lv_dashboard]
==================================================
*/

/* ------------------------------------------------
CREATE DATABASE TABLES
------------------------------------------------ */

add_action('login_init', function () {

    wp_redirect(home_url('/login'));
    exit;

});

add_action('template_redirect', function () {

    if (is_page('login') && is_user_logged_in()) {
        wp_redirect(home_url('/dashboard'));
        exit;
    }

});

add_action('init', function () {

    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $table_links = $wpdb->prefix . 'lv_links';
    $table_clicks = $wpdb->prefix . 'lv_clicks';

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $sql1 = "CREATE TABLE IF NOT EXISTS $table_links (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        title VARCHAR(255),
        url TEXT,
        sort_order INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $sql2 = "CREATE TABLE IF NOT EXISTS $table_clicks (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        link_id BIGINT UNSIGNED NOT NULL,
        ip_address VARCHAR(100),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    dbDelta($sql1);
    dbDelta($sql2);

});

/* ------------------------------------------------
DASHBOARD SHORTCODE
------------------------------------------------ */

/* 
==================================================
LINKVERSE - SIMPLE LINKTREE CLONE
==================================================
SHORTCODES:
[lv_register]
[lv_login]
[lv_dashboard]
==================================================
*/

/* ------------------------------------------------
CREATE DATABASE TABLES
------------------------------------------------ */

add_action('init', function () {

    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $table_links = $wpdb->prefix . 'lv_links';
    $table_clicks = $wpdb->prefix . 'lv_clicks';

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $sql1 = "CREATE TABLE IF NOT EXISTS $table_links (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        title VARCHAR(255),
        url TEXT,
        sort_order INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $sql2 = "CREATE TABLE IF NOT EXISTS $table_clicks (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        link_id BIGINT UNSIGNED NOT NULL,
        ip_address VARCHAR(100),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    dbDelta($sql1);
    dbDelta($sql2);

});

add_action('init', function () {

    if (!is_user_logged_in()) return;

    if (isset($_POST['lv_save_profile'])) {

        $user_id = get_current_user_id();

        if (!empty($_POST['profile_pic'])) {
            update_user_meta(
                $user_id,
                'lv_profile_pic',
                esc_url_raw($_POST['profile_pic'])
            );
        }

        // redirect to prevent resubmit
        wp_redirect(home_url('/dashboard'));
        exit;
    }

});
/* ------------------------------------------------
REGISTER SHORTCODE
------------------------------------------------ */

add_shortcode('lv_register', function () {

    if (is_user_logged_in()) {
        return "You are already logged in.";
    }

    if (isset($_POST['lv_register'])) {

        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];

        $user_id = wp_create_user($username, $password, $email);

        if (!is_wp_error($user_id)) {

            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id);

            wp_redirect(home_url('/dashboard'));
            exit;

        } else {

            echo "<p>".$user_id->get_error_message()."</p>";

        }
    }

    ob_start();
    ?>

    <form method="POST" class="lv-form">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="lv_register">Register</button>
    </form>

    <?php
    return ob_get_clean();
});


/* ------------------------------------------------
LOGIN SHORTCODE
------------------------------------------------ */

add_shortcode('lv_login', function () {

    if (is_user_logged_in()) {
        return "Already logged in.";
    }

    if (isset($_POST['lv_login'])) {

        $creds = array(
            'user_login' => $_POST['username'],
            'user_password' => $_POST['password'],
            'remember' => true
        );

        $user = wp_signon($creds, false);

        if (!is_wp_error($user)) {

            wp_redirect(home_url('/dashboard'));
            exit;

        } else {

            echo "<p>Invalid login.</p>";

        }
    }

    ob_start();
    ?>

    <form method="POST" class="lv-form">
        <input type="text" name="username" placeholder="Username">
        <input type="password" name="password" placeholder="Password">
        <button type="submit" name="lv_login">Login</button>
    </form>

    <?php
    return ob_get_clean();
});


/* ------------------------------------------------
DASHBOARD SHORTCODE
------------------------------------------------ */

add_shortcode('lv_dashboard', function () {

    if (!is_user_logged_in()) {
        return "Please login.";
    }

    global $wpdb;

    $user_id = get_current_user_id();
    $table_links = $wpdb->prefix . 'lv_links';
    $table_clicks = $wpdb->prefix . 'lv_clicks';

    /* ADD LINK */

    if (isset($_POST['lv_add_link'])) {

        $wpdb->insert($table_links, array(
            'user_id' => $user_id,
            'title' => sanitize_text_field($_POST['title']),
            'url' => esc_url_raw($_POST['url'])
        ));

    }
	
	/* EDIT LINK */
if (isset($_POST['lv_update_link'])) {

    $link_id = intval($_POST['link_id']);

    $wpdb->update(
        $table_links,
        array(
            'title' => sanitize_text_field($_POST['title']),
            'url'   => esc_url_raw($_POST['url'])
        ),
        array(
            'id' => $link_id,
            'user_id' => $user_id
        )
    );

    // ✅ IMPORTANT: redirect to remove ?edit parameter
    wp_redirect(home_url('/dashboard'));
    exit;
}

    /* DELETE LINK */

    if (isset($_GET['delete'])) {

        $wpdb->delete($table_links, array(
            'id' => intval($_GET['delete']),
            'user_id' => $user_id
        ));

    }

    $links = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_links WHERE user_id=%d ORDER BY id DESC",
            $user_id
        )
    );
	
	$edit_link = null;

if (isset($_GET['edit'])) {

    $edit_id = intval($_GET['edit']);

    $edit_link = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_links WHERE id=%d AND user_id=%d",
            $edit_id,
            $user_id
        )
    );
}

/* START OUTPUT BUFFER AFTER ALL PHP LOGIC */
ob_start();
?>
 
<?php
$current_user = wp_get_current_user();
$profile_pic = get_user_meta($user_id, 'lv_profile_pic', true);
?>
<div class="lv-section">
<h2>Profile Settings</h2>

<form method="POST" class="lv-form">

    <input type="url" name="profile_pic" placeholder="Profile Picture URL"
           value="<?php echo esc_url($profile_pic); ?>">

    <button type="submit" name="lv_save_profile">
        Save Profile
    </button>

</form>




    <div class="lv-dashboard">

        <h2>Your Public Page</h2>

        <p>
            <a href="<?php echo home_url('/u/' . wp_get_current_user()->user_login); ?>" target="_blank">
                View Profile
            </a>
        </p>

<form method="POST" class="lv-form">

    <input type="hidden" name="link_id" value="<?php echo $edit_link->id ?? ''; ?>">

    <input type="text" name="title" placeholder="Link Title"
           value="<?php echo esc_attr($edit_link->title ?? ''); ?>" required>

    <input type="url" name="url" placeholder="https://example.com"
           value="<?php echo esc_url($edit_link->url ?? ''); ?>" required>

    <?php if ($edit_link): ?>

        <button type="submit" name="lv_update_link">Update Link</button>
        <a href="<?php echo home_url('/dashboard'); ?>">Cancel</a>

    <?php else: ?>

        <button type="submit" name="lv_add_link">Add Link</button>

    <?php endif; ?>

</form>
		</div>

        <hr>
<div class="lv-section">
        <h3>Your Links</h3>

        <?php foreach($links as $link): ?>

            <?php
            $clicks = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_clicks WHERE link_id=%d",
                    $link->id
                )
            );
            ?>

<div class="lv-item" data-id="<?php echo $link->id; ?>">

    <!-- NORMAL VIEW -->
    <div class="lv-view">
        <strong class="lv-title"><?php echo esc_html($link->title); ?></strong>
        <br>
        <small class="lv-url"><?php echo esc_url($link->url); ?></small>
        <br>
        Clicks: <?php echo $clicks; ?>
        <br><br>

        <button class="lv-edit">Edit</button>
        <a href="?delete=<?php echo $link->id; ?>">Delete</a>
    </div>

    <!-- INLINE EDIT FORM (PUT IT HERE) -->
    <div class="lv-edit-form" style="display:none;">
        <input type="text" class="edit-title" value="<?php echo esc_attr($link->title); ?>">
        <input type="url" class="edit-url" value="<?php echo esc_url($link->url); ?>">

        <button class="lv-save">Save</button>
        <button class="lv-cancel">Cancel</button>
    </div>

</div>

            <hr>

        <?php endforeach; ?>
	</div>

    </div>

    

<script>
document.addEventListener('click', function(e) {

    /* OPEN EDIT */
    if (e.target.classList.contains('lv-edit')) {
        const item = e.target.closest('.lv-item');

        item.querySelector('.lv-view').style.display = 'none';
        item.querySelector('.lv-edit-form').style.display = 'block';
    }

    /* CANCEL EDIT */
    if (e.target.classList.contains('lv-cancel')) {
        const item = e.target.closest('.lv-item');

        item.querySelector('.lv-view').style.display = 'block';
        item.querySelector('.lv-edit-form').style.display = 'none';
    }

    /* SAVE EDIT (AJAX) */
    if (e.target.classList.contains('lv-save')) {

        const item = e.target.closest('.lv-item');

        const id = item.dataset.id;
        const title = item.querySelector('.edit-title').value;
        const url = item.querySelector('.edit-url').value;

        const formData = new FormData();
        formData.append('action', 'lv_update_link');
        formData.append('link_id', id);
        formData.append('title', title);
        formData.append('url', url);

        fetch("<?php echo admin_url('admin-ajax.php'); ?>", {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {

            if (res.success) {

                item.querySelector('.lv-title').innerText = res.data.title;
                item.querySelector('.lv-url').innerText = res.data.url;

                item.querySelector('.lv-view').style.display = 'block';
                item.querySelector('.lv-edit-form').style.display = 'none';

            } else {
                alert('Update failed');
            }

        });

    }

});
</script>

<?php

    return ob_get_clean();

});


/* ------------------------------------------------
PUBLIC PROFILE PAGE
URL: /u/username
------------------------------------------------ */

add_action('init', function () {

    add_rewrite_rule(
        '^u/([^/]*)/?',
        'index.php?lv_user=$matches[1]',
        'top'
    );

});

add_filter('query_vars', function ($vars) {

    $vars[] = 'lv_user';
    return $vars;

});

add_action('template_redirect', function () {

    $username = get_query_var('lv_user');

    if (!$username) {
        return;
    }

    $user = get_user_by('login', $username);

    if (!$user) {
        wp_die('User not found.');
    }

    global $wpdb;

    $table_links = $wpdb->prefix . 'lv_links';
    $table_clicks = $wpdb->prefix . 'lv_clicks';

    $links = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_links WHERE user_id=%d ORDER BY id DESC",
            $user->ID
        )
    );

    ?>
    
    <!DOCTYPE html>
    <html>
    <head>
        <title><?php echo esc_html($user->display_name); ?></title>

        <style>

            body{
                font-family:Arial;
                background:#111827;
                color:white;
                padding:40px;
                text-align:center;
            }

            .lv-container{
                max-width:500px;
                margin:auto;
            }

            .lv-link{
                display:block;
                background:white;
                color:#111;
                padding:16px;
                margin-bottom:15px;
                border-radius:12px;
                text-decoration:none;
                font-weight:bold;
                transition:0.3s;
            }

            .lv-link:hover{
                transform:scale(1.03);
            }

        </style>

    </head>
    <body>

    <div class="lv-container">

        <h1>@<?php echo esc_html($user->user_login); ?></h1>

        <?php foreach($links as $link): ?>

            <a class="lv-link"
               href="<?php echo home_url('/go/' . $link->id); ?>">
                <?php echo esc_html($link->title); ?>
            </a>

        <?php endforeach; ?>

    </div>
		
<!-- STICKY CTA BUTTON -->
<div style="
    position:fixed;
    bottom:20px;
    left:50%;
    transform:translateX(-50%);
    z-index:9999;
">

    <a href="<?php echo home_url(); ?>" 
       target="_blank" 
       rel="noopener noreferrer"
       style="
            display:inline-block;
            padding:12px 20px;
            background:#ffffff;
            color:#111;
            border-radius:999px;
            font-weight:bold;
            text-decoration:none;
            box-shadow:0 8px 20px rgba(0,0,0,0.25);
            font-size:14px;
            white-space:nowrap;
       ">
        🚀 Join MyBio Page
    </a>

</div>

    </body>
    </html>

    <?php
    exit;

});


/* ------------------------------------------------
TRACK CLICKS
/go/ID
------------------------------------------------ */

add_action('init', function () {

    add_rewrite_rule(
        '^go/([0-9]+)/?',
        'index.php?lv_go=$matches[1]',
        'top'
    );

});

add_filter('query_vars', function ($vars) {

    $vars[] = 'lv_go';
    return $vars;

});

add_action('template_redirect', function () {

    $link_id = get_query_var('lv_go');

    if (!$link_id) {
        return;
    }

    global $wpdb;

    $table_links = $wpdb->prefix . 'lv_links';
    $table_clicks = $wpdb->prefix . 'lv_clicks';

    $link = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_links WHERE id=%d",
            $link_id
        )
    );

    if (!$link) {
        wp_die('Invalid link.');
    }

    $wpdb->insert($table_clicks, array(
        'link_id' => $link_id,
        'ip_address' => $_SERVER['REMOTE_ADDR']
    ));

    wp_redirect($link->url);
    exit;

});


/* ------------------------------------------------
STYLES
------------------------------------------------ */

add_action('wp_head', function () {
?>

<style>


	
	body{
    font-family:-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial;
    background:#f5f7fb;
    margin:0;
}

/* Center auth pages */
.lv-form{
    max-width:420px;
    margin:60px auto;
    background:#fff;
    padding:30px;
    border-radius:18px;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
}

/* Inputs */
.lv-form input{
    width:100%;
    padding:14px 16px;
    margin-bottom:12px;
    border:1px solid #e5e7eb;
    border-radius:12px;
    font-size:14px;
    transition:0.2s;
}

.lv-form input:focus{
    border-color:#437a65;
    box-shadow:0 0 0 3px rgba(67,122,101,0.15);
    outline:none;
}

/* Button */
.lv-form button{
    width:100%;
    padding:14px;
    border:none;
    border-radius:12px;
    background:#437a65;
    color:#fff;
    font-weight:600;
    cursor:pointer;
    transition:0.2s;
}

.lv-form button:hover{
    background:#356353;
    transform:translateY(-1px);
}

/* Dashboard wrapper */
.lv-dashboard{
    max-width:800px;
    margin:40px auto;
    padding:0 20px;
}

/* Section card */
.lv-section{
    background:#fff;
    padding:20px;
    border-radius:16px;
    box-shadow:0 10px 25px rgba(0,0,0,0.05);
    margin-bottom:20px;
}

/* Link cards */
.lv-item{
    background:#fff;
    border:1px solid #eee;
    padding:16px;
    border-radius:14px;
    margin-bottom:12px;
    transition:0.2s;
}

.lv-item:hover{
    transform:translateY(-2px);
    box-shadow:0 8px 20px rgba(0,0,0,0.06);
}

/* Link title */
.lv-title{
    font-weight:600;
    color:#111827;
}

/* URL */
.lv-url{
    font-size:12px;
    color:#6b7280;
}

/* Buttons */
.lv-edit, .lv-save, .lv-cancel{
    padding:6px 10px;
    border-radius:8px;
    border:none;
    font-size:12px;
    cursor:pointer;
}

.lv-edit{ background:#e5f0eb; color:#437a65; }
.lv-save{ background:#437a65; color:#fff; }
.lv-cancel{ background:#f3f4f6; }

</style>

<?php
});

add_action('wp_ajax_lv_update_link', function () {

    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
    }

    global $wpdb;

    $table_links = $wpdb->prefix . 'lv_links';
    $user_id = get_current_user_id();

    $link_id = intval($_POST['link_id']);

    $updated = $wpdb->update(
        $table_links,
        array(
            'title' => sanitize_text_field($_POST['title']),
            'url'   => esc_url_raw($_POST['url'])
        ),
        array(
            'id' => $link_id,
            'user_id' => $user_id
        )
    );

    if ($updated !== false) {
        wp_send_json_success(array(
            'title' => $_POST['title'],
            'url'   => $_POST['url']
        ));
    } else {
        wp_send_json_error('Update failed');
    }
});


/* ------------------------------------------------
FLUSH REWRITE RULES
Visit:
Settings > Permalinks > Save Changes
------------------------------------------------ */


/* ------------------------------------------------
PUBLIC PROFILE PAGE
URL: /u/username
------------------------------------------------ */

add_action('init', function () {

    add_rewrite_rule(
        '^u/([^/]*)/?',
        'index.php?lv_user=$matches[1]',
        'top'
    );

});

add_filter('query_vars', function ($vars) {

    $vars[] = 'lv_user';
    return $vars;

});

add_action('template_redirect', function () {

    $username = get_query_var('lv_user');

    if (!$username) {
        return;
    }

    $user = get_user_by('login', $username);

    if (!$user) {
        wp_die('User not found.');
    }

    global $wpdb;

    $table_links = $wpdb->prefix . 'lv_links';
    $table_clicks = $wpdb->prefix . 'lv_clicks';

    $links = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_links WHERE user_id=%d ORDER BY id DESC",
            $user->ID
        )
    );

    ?>
    
    <!DOCTYPE html>
    <html>
    <head>
        <title><?php echo esc_html($user->display_name); ?></title>

        <style>

            body{
                font-family:Arial;
                background:#111827;
                color:white;
                padding:40px;
                text-align:center;
            }

            .lv-container{
                max-width:500px;
                margin:auto;
            }

            .lv-link{
                display:block;
                background:white;
                color:#111;
                padding:16px;
                margin-bottom:15px;
                border-radius:12px;
                text-decoration:none;
                font-weight:bold;
                transition:0.3s;
            }

            .lv-link:hover{
                transform:scale(1.03);
            }

        </style>

    </head>
    <body>

    <div class="lv-container">

    <h1>@<?php echo esc_html($user->user_login); ?></h1>

<?php
$profile_pic = get_user_meta($user->ID, 'lv_profile_pic', true);
?>

<div style="text-align:center; margin-bottom:20px;">

    <?php if (!empty($profile_pic)) : ?>
        <img src="<?php echo esc_url($profile_pic); ?>"
             style="width:100px;height:100px;border-radius:50%;object-fit:cover;">
    <?php else : ?>
        <div style="width:100px;height:100px;border-radius:50%;background:#333;margin:0 auto;"></div>
    <?php endif; ?>

</div>

    <?php foreach($links as $link): ?>

            <a class="lv-link"
               href="<?php echo home_url('/go/' . $link->id); ?>">
                <?php echo esc_html($link->title); ?>
            </a>

        <?php endforeach; ?>

    </div>

    </body>
    </html>

    <?php
    exit;

});


/* ------------------------------------------------
TRACK CLICKS
/go/ID
------------------------------------------------ */

add_action('init', function () {

    add_rewrite_rule(
        '^go/([0-9]+)/?',
        'index.php?lv_go=$matches[1]',
        'top'
    );

});

add_filter('query_vars', function ($vars) {

    $vars[] = 'lv_go';
    return $vars;

});

add_action('template_redirect', function () {

    $link_id = get_query_var('lv_go');

    if (!$link_id) {
        return;
    }

    global $wpdb;

    $table_links = $wpdb->prefix . 'lv_links';
    $table_clicks = $wpdb->prefix . 'lv_clicks';

    $link = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_links WHERE id=%d",
            $link_id
        )
    );

    if (!$link) {
        wp_die('Invalid link.');
    }

    $wpdb->insert($table_clicks, array(
        'link_id' => $link_id,
        'ip_address' => $_SERVER['REMOTE_ADDR']
    ));

    wp_redirect($link->url);
    exit;

});


/* ------------------------------------------------
STYLES
------------------------------------------------ */

add_action('wp_head', function () {
?>

<style>

.lv-form{
    max-width:400px;
    margin:auto;
}

.lv-form input{
    width:100%;
    padding:14px;
    margin-bottom:10px;
    border:1px solid #ddd;
    border-radius:10px;
}

.lv-form button{
    width:100%;
    padding:14px;
    border:none;
    background:#111827;
    color:white;
    border-radius:10px;
    cursor:pointer;
}

.lv-dashboard{
    max-width:700px;
    margin:auto;
}

.lv-item{
    background:#f9f9f9;
    padding:20px;
    border-radius:12px;
}

</style>

<?php
});

add_action('wp_ajax_lv_update_link', function () {

    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
    }

    global $wpdb;

    $table_links = $wpdb->prefix . 'lv_links';
    $user_id = get_current_user_id();

    $link_id = intval($_POST['link_id']);

    $updated = $wpdb->update(
        $table_links,
        array(
            'title' => sanitize_text_field($_POST['title']),
            'url'   => esc_url_raw($_POST['url'])
        ),
        array(
            'id' => $link_id,
            'user_id' => $user_id
        )
    );

    if ($updated !== false) {
        wp_send_json_success(array(
            'title' => $_POST['title'],
            'url'   => $_POST['url']
        ));
    } else {
        wp_send_json_error('Update failed');
    }
});


/* ------------------------------------------------
FLUSH REWRITE RULES
Visit:
Settings > Permalinks > Save Changes
------------------------------------------------ */