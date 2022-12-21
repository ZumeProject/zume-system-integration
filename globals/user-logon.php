<?php

function zume_user_login_widget() {
    ?>
    <div style="text-align: center;">

        <button class="button" id="jwt_register_user">Register User</button><br>
        <div style="text-align:center; margin: 0 auto; width:200px;"><input type="text" id="username" placeholder="Username" value="" /> </div>
        <div style="text-align:center; margin: 0 auto; width:200px;"><input type="password" id="password" placeholder="Password" value="" /> </div>
        <button class="button" id="jwt_login">Login</button><br>
        <button class="button" id="jwt_logout">Logout</button><br>
        <hr>
        <button class="button" id="get_me">Get Me</button><br>
        <hr>
        <div id="response"></div>
        <hr>
    </div>

    <script>
        jQuery(document).ready(function(){

            window.jsObject = [<?php echo json_encode([
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'translations' => [
                    'add' => __( 'Add Magic', 'prayer-global' ),
                ],
            ]) ?>][0]

            window.user_object = false

            if ( typeof localStorage.zume_token !== 'undefined' ) {
                jQuery('.login-logo').show()
                jQuery('.u_name').html( localStorage.zume_name )
            }

            window.api_post = ( action, data ) => {
                return jQuery.ajax({
                    type: "POST",
                    data: JSON.stringify({ action: action, data: data }),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    url: window.jsObject.root + 'dt-login/v1/login',
                    beforeSend: function (xhr) {
                        if (localStorage.zume_token) {
                            xhr.setRequestHeader('Authorization', 'Bearer ' + localStorage.zume_token);
                        }
                    }
                })
                    .fail(function(e) {
                        console.log(e)
                    })
            }
            window.api_remote_post = ( endpoint, data ) => {
                return jQuery.ajax({
                    type: "POST",
                    data: JSON.stringify(data),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    url: `https://zume5.training/tools/wp-json/`+endpoint,
                    beforeSend: function (xhr) {
                        if (localStorage.zume_token) {
                            xhr.setRequestHeader('Authorization', 'Bearer ' + localStorage.zume_token);
                        }
                    }
                })
                    .fail(function(e) {
                        console.log(e)
                    })
            }
            window.api_remote_get = ( endpoint ) => {
                return jQuery.ajax({
                    type: "GET",
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    url: `https://zume5.training/tools/wp-json/`+endpoint,
                    beforeSend: function (xhr) {
                        if (localStorage.zume_token) {
                            xhr.setRequestHeader('Authorization', 'Bearer ' + localStorage.zume_token);
                        }
                    }
                })
                    .fail(function(e) {
                        console.log(e)
                    })
            }
            jQuery('#get_me').click(function(){
                window.api_remote_get('dt/v1/user/my')
                    .done(function(data){
                        console.log(data);
                        jQuery('#response').html(
                            JSON.stringify(data)
                        )
                    })
            })
            jQuery('#jwt_register_user').click(function(){
                const d = new Date();
                window.time = d.getTime();
                let new_user_params = {
                    'user-email': window.time + '@email.com',
                    'user-display': window.time,
                    'user-password': window.time,
                    'locale': 'en'
                }
                window.api_remote_post('dt/v1/users/register', new_user_params)
                    .done(function(data){
                        console.log(data)
                        window.user_object = data
                        localStorage.zume_token = data.token
                        localStorage.zume_name = data.user_display_name
                        jQuery('.login-logo').show()
                        jQuery('.u_name').html( data.user_display_name )
                        jQuery('#response').html(JSON.stringify(data))
                    })
            })
            jQuery('#jwt_login').click(function() {
                let username = jQuery('#username').val()
                let password = jQuery('#password').val()
                jQuery.ajax({
                    type: "POST",
                    url: "https://zume5.training/tools/wp-json/jwt-auth/v1/token",
                    data: {
                        username: username,
                        password: password
                    },
                    success: function(data) {
                        console.log(data)
                        window.user_object = data
                        localStorage.zume_token = data.token
                        localStorage.zume_name = data.user_display_name
                        jQuery('.login-logo').show()
                        jQuery('.u_name').html( data.user_display_name )
                        jQuery('#response').html(JSON.stringify(data))
                    },
                    error: function() {
                        alert("Login Failed");
                    }
                });
            });
            jQuery('#jwt_logout').click(function() {
                jQuery('.login-logo').hide()
                localStorage.removeItem('zume_token')
                localStorage.removeItem('zume_name')
            });
        })
    </script>
    <?php
}
add_shortcode('zume_login_widget', 'zume_user_login_widget' );
?>