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
add_action('user_register', function($user_id){

    $user = get_userdata($user_id);

    $profile_url = home_url('/u/' . $user->user_login);
    $dashboard_url = home_url('/dashboard');

    $subject = 'Welcome to MyBio 🚀 Your Page is Ready!';

    $message = '
    <div style="font-family:Arial,sans-serif;line-height:1.6;color:#111">

        <h2 style="color:#111827;">Welcome to MyBio 🚀</h2>

        <p>Hi <b>'.$user->user_login.'</b>,</p>

        <p>Your account has been successfully created and your bio page is now live.</p>

        <hr>

        <h3>🔗 Your Links</h3>

        <p>
            👉 Public Page:<br>
            <a href="'.$profile_url.'">'.$profile_url.'</a>
        </p>

        <p>
            👉 Manage Your Links:<br>
            <a href="'.$dashboard_url.'">'.$dashboard_url.'</a>
        </p>

        <hr>

        <h3>✨ What you can do now:</h3>
        <ul>
            <li>Add unlimited links</li>
            <li>Customize your profile</li>
            <li>Share your page anywhere</li>
            <li>Track link clicks</li>
        </ul>

        <p style="margin-top:20px;">
            Start building your online presence in one simple page.
        </p>

        <p><b>- MyBio Team</b></p>

    </div>
    ';

    $headers = array(
        'Content-Type: text/html; charset=UTF-8'
    );

    wp_mail($user->user_email, $subject, $message, $headers);

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

<div class="lv-dashboard-wrap">

    <!-- HEADER -->
    <div class="lv-header-card">

        <div class="lv-profile">
            <?php
            $profile_pic = get_user_meta($user_id, 'lv_profile_pic', true);
            ?>

            <?php if (!empty($profile_pic)) : ?>
                <img src="<?php echo esc_url($profile_pic); ?>" class="lv-avatar">
            <?php else : ?>
                <div class="lv-avatar lv-avatar-fallback"></div>
            <?php endif; ?>

            <div class="lv-user-meta">
                <h2>@<?php echo esc_html(wp_get_current_user()->user_login); ?></h2>
                <p>Your link-in-bio dashboard</p>
            </div>
        </div>

        <a class="lv-view-btn" target="_blank"
           href="<?php echo home_url('/u/' . wp_get_current_user()->user_login); ?>">
            View Public Page →
        </a>

    </div>

    

    <!-- ADD / EDIT LINK -->
    <div class="lv-card">

        <h3><?php echo $edit_link ? 'Edit Link' : 'Add New Link'; ?></h3>

        <form method="POST" class="lv-form-clean">

            <input type="hidden" name="link_id" value="<?php echo $edit_link->id ?? ''; ?>">

            <input type="text" name="title" placeholder="Link Title"
                   value="<?php echo esc_attr($edit_link->title ?? ''); ?>" required>

            <input type="url" name="url" placeholder="https://example.com"
                   value="<?php echo esc_url($edit_link->url ?? ''); ?>" required>

            <?php if ($edit_link): ?>
                <button type="submit" name="lv_update_link">Update Link</button>
                <a class="lv-cancel-btn" href="<?php echo home_url('/dashboard'); ?>">Cancel</a>
            <?php else: ?>
                <button type="submit" name="lv_add_link">Add Link</button>
            <?php endif; ?>

        </form>

    </div>

    <!-- LINKS -->
    <div class="lv-card">

        <h3>Your Links</h3>

        <div class="lv-links-grid">

            <?php foreach($links as $link): ?>

                <?php
                $clicks = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT COUNT(*) FROM $table_clicks WHERE link_id=%d",
                        $link->id
                    )
                );
                ?>

                <div class="lv-link-card">

                    <div>
                        <div class="lv-title"><?php echo esc_html($link->title); ?></div>
                        <div class="lv-url"><?php echo esc_html($link->url); ?></div>
                        <div class="lv-meta">Clicks: <?php echo $clicks; ?></div>
                    </div>

                    <div class="lv-actions">
                        <a href="?edit=<?php echo $link->id; ?>">Edit</a>
                        <a href="?delete=<?php echo $link->id; ?>" onclick="return confirm('Delete this link?')">Delete</a>
                    </div>

                </div>

            <?php endforeach; ?>

        </div>

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
.lv-dashboard-wrap{
    max-width:800px;
    margin:40px auto;
    padding:0 16px;
    font-family:system-ui, Arial;
}

/* CARDS */
.lv-card{
    background:#fff;
    border-radius:16px;
    padding:20px;
    margin-bottom:16px;
    box-shadow:0 6px 20px rgba(0,0,0,0.06);
}

/* HEADER */
.lv-header-card{
    background:#111827;
    color:#fff;
    padding:20px;
    border-radius:16px;
    margin-bottom:16px;
}

.lv-profile{
    display:flex;
    align-items:center;
    gap:14px;
}

.lv-avatar{
    width:60px;
    height:60px;
    border-radius:50%;
    object-fit:cover;
}

.lv-avatar-fallback{
    background:#374151;
}

.lv-user-meta h2{
    margin:0;
    font-size:18px;
}

.lv-user-meta p{
    margin:2px 0 0;
    font-size:12px;
    opacity:0.7;
}

/* VIEW BUTTON */
.lv-view-btn{
    display:inline-block;
    margin-top:12px;
    color:#111827;
    background:#fff;
    padding:10px 14px;
    border-radius:999px;
    text-decoration:none;
    font-weight:600;
}

/* FORMS */
.lv-form-clean input{
    width:100%;
    padding:12px;
    margin-bottom:10px;
    border:1px solid #e5e7eb;
    border-radius:10px;
}

.lv-form-clean button{
    width:100%;
    padding:12px;
    background:#111827;
    color:#fff;
    border:none;
    border-radius:10px;
    cursor:pointer;
}

/* LINKS GRID */
.lv-links-grid{
    display:flex;
    flex-direction:column;
    gap:10px;
}

.lv-link-card{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:14px;
    border:1px solid #eee;
    border-radius:12px;
    background:#fafafa;
}

.lv-title{
    font-weight:600;
}

.lv-url{
    font-size:12px;
    color:#6b7280;
}

.lv-meta{
    font-size:11px;
    color:#9ca3af;
}

.lv-actions a{
    margin-left:10px;
    font-size:12px;
    text-decoration:none;
    color:#111827;
}

.lv-actions a:hover{
    text-decoration:underline;
}

.lv-cancel-btn{
    display:block;
    text-align:center;
    margin-top:8px;
    font-size:13px;
    color:#6b7280;
}

.lv-dashboard-wrap{
    max-width:800px;
    margin:40px auto;
    padding:0 16px;
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
FLUSH REWRITE RULES
Visit:
Settings > Permalinks > Save Changes
------------------------------------------------ */