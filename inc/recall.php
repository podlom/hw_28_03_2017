<?php
/**
 * Created by PhpStorm.
 * User: 23
 * Date: 28.03.17
 * Time: 19:00
 */

global $siteHeader;

$siteHeader = 'Recalls';




//$commentsDbFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'comments.dat';


$errorMessages = [];

if (!empty($_POST)) {
    /* echo '<pre>' . var_export($_POST, 1) . '</pre>';
    exit; */
    $canAddComment = false;
    if (empty($_POST['uname'])) {
        $errorMessages[] = '<p class="err">Username can`t be empty</p>';
    }
    if (empty($_POST['uemail'])) {
        $errorMessages[] = '<p class="err">Email can`t be empty</p>';
    }
    if (!filter_var($_POST['uemail'], FILTER_VALIDATE_EMAIL)) {
        $errorMessages[] = '<p class="err">Email has wrong format</p>';
    }
    if (empty($_POST['ucomment'])) {
        $errorMessages[] = '<p class="err">Comment text can`t be empty</p>';
    }
    if (empty($_POST['g-recaptcha-response'])) {
        $errorMessages[] = '<p class="err">Captcha text can`t be empty</p>';
    } else {
        $reqData = http_build_query([
            'secret' => '6LdCsBoUAAAAABK8ivUCIPNIMCCiDyGOtUfAQiZ1',
            'response' => $_POST['g-recaptcha-response'],
            // remoteip => $_SERVER['REMOTE_ADDR'],
        ]);
        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $reqData,
            ]
        ]);
        $checkCaptchaRes = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
        // echo '$checkCaptchaRes: ' . var_export($checkCaptchaRes, 1) . '<br>';
        // exit;
        // '{ "success": true, "challenge_ts": "2017-03-28T17:48:12Z", "hostname": "localhost" }'
        $jsObj = json_decode($checkCaptchaRes);
        if (!empty($jsObj) && $jsObj->success) {
            // reCaptcha is OK
            $canAddComment = true;
        } else {
            $errorMessages[] = '<p class="err">Wrong reCaptcha code</p>';
        }

    }
    if (empty($errorMessages) && $canAddComment) {
        addComment($_POST);
    }
}

function readComments() {
    $commentsData = [];
    global $commentsDbFile;
    if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'comments.dat')) {
        $commentsData = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'comments.dat');
        if ($commentsData !== false) {
            $commentsData = unserialize($commentsData);
        }
    }
    return $commentsData;
}

function addComment($data) {
    global $commentsDbFile;
    $aOld = readComments();
    $data['AddedDt'] = date('Y-m-d H:i:s');
    $newData = [];
    if (is_array($aOld)) {
        $newData = array_merge($aOld, [$data]);
    } else {
        $newData[] = $data;
    }
    $sData = serialize($newData);

    file_put_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'comments.dat', $sData);

}

function antimat($str) {
    return str_replace([
        'Test',
        // 'comment',
    ], [
        'Tost',
        // '*compliement*',
    ], $str);
}

function getComments() {
    $aComm = readComments();
    if (!empty($aComm)) {
        foreach ($aComm as $c) {
            $eP = explode('@', $c['uemail']);
            echo '<dl><dt>Commented by: <script type="text/javascript"> jep_link("'. $eP[1] .
                '","' . $eP[0], '","' . $c['uname'] . '"); </script>' .
                ' on ' . $c['AddedDt'] . '</dt><dd>' .
                antimat($c['ucomment']) .
                '</dd></dl>';
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Contact form</title>
    <script src="inc/vendor/jep.js" type="text/javascript"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
<?php
    if (!empty($errorMessages)) {
        foreach ($errorMessages as $msg) {
            echo $msg . '<br>';
        }
    }
?>
<form id="commentForm1" action="" method="post">
    <div>
        <label for="uname">Username *:</label>
        <input required type="text" name="uname">
    </div>
    <div>
        <label for="uemail">Email *:</label>
        <input required type="email" name="uemail">
    </div>
    <div>
        <label for="ucomment">Comment *:</label><br>
        <textarea required name="ucomment" placeholder="Inout your comment"></textarea>
    </div>
    <div>
        <button
            class="g-recaptcha"
            data-sitekey="6LdCsBoUAAAAAFjvPvA7xSFg576uRPISksKIzTJj"
            data-callback="myCommentSubmit">
            Add comment
        </button>
        <script>
            function myCommentSubmit(token) {
                document.getElementById("commentForm1").submit();
            }
        </script>
    </div>
    <div>* - required fields</div>
</form>
<hr>
<?php
    getComments();
?>
</body>
</html>