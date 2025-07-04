<?php

class q2a_send_email_page
{
    var $directory;
    var $urltoroot;

    function load_module($directory, $urltoroot)
    {
        $this->directory = $directory;
        $this->urltoroot = $urltoroot;
    }

    function suggest_requests()
    {
        return array(
            array(
                'title' => 'Send Email',
                'request' => 'sendmail',
                'nav' => 'M',
            ),
        );
    }

    function match_request($request)
    {
        return $request === 'sendmail';
    }

    function process_request($request)
    {
        require_once QA_INCLUDE_DIR . 'app/emails.php';

        // Start output buffering
        ob_start();

        // Only allow admin
        if (qa_get_logged_in_level() < QA_USER_LEVEL_ADMIN) {
            $qa_content = qa_content_prepare();
            $qa_content['error'] = 'You must be an admin to use this feature.';
            $qa_content['custom'] = ob_get_clean();
            return $qa_content;
        }

        $qa_content = qa_content_prepare();
        $qa_content['title'] = 'Send Email';

        // Process form
        if (isset($_POST['send_email'])) {
            $username = trim($_POST['username']);
            $message = trim($_POST['message']);

            if (empty($username) || empty($message)) {
                echo "<p style='color:red;'>⚠️ All fields are required.</p>";
            } else {
                $useremail = $this->get_user_email_by_username($username);

                if ($useremail) {
                    $subject = 'Message from Admin';

                    $email_sent = qa_send_email(array(
                        'fromemail' => qa_opt('from_email'),
                        'fromname'  => qa_opt('site_title'),
                        'toemail'   => $useremail,
                        'toname'    => $username,
                        'subject'   => $subject,
                        'body'      => $message,
                        'html'      => false,
                    ));

                    if ($email_sent) {
                        echo "<p style='color:green;'>✅ Email sent successfully to <strong>$username</strong>.</p>";
                    } else {
                        echo "<p style='color:red;'>❌ Failed to send the email.</p>";
                    }
                } else {
                    echo "<p style='color:red;'>❌ User not found.</p>";
                }
            }
        }

        // Form HTML
        echo '
        <style>
            form { width: 100%; display: flex; flex-direction: column; gap: 15px; }
            label { margin-bottom: 5px; font-size: 14px; }
            input, textarea {
                width: 100%; padding: 10px; box-sizing: border-box; font-size: 14px;
            }
            textarea { height: 150px; }
        </style>

        <form method="post" action="' . qa_self_html() . '">
            <div>
                <label for="username">User Name:</label>
                <input type="text" name="username" id="username" placeholder="Enter username">
            </div>
            <div>
                <label for="message">Message:</label>
                <textarea name="message" id="message" placeholder="Type your message"></textarea>
            </div>
            <div>
                <input type="submit" name="send_email" value="Send Email" class="qa-form-basic-button">
            </div>
        </form>';

        // End output buffering and assign to qa_content
        $qa_content['custom'] = ob_get_clean();

        return $qa_content;
    }

    // Helper function to get email from username
    function get_user_email_by_username($username)
    {
        $result = qa_db_query_sub(
            'SELECT email FROM ^users WHERE handle = $',
            $username
        );

        $row = qa_db_read_one_assoc($result, true);
        return $row ? $row['email'] : false;
    }
}
