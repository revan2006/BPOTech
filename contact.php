<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; //Cần tìm hiểu
// ==============================================================
// Thiết lập thông tin cơ bản
// ==============================================================

// Email gửi
$to = "langphongmtb@gmail.com";

// Chữ ký khi hoàn thành
$sign1 = "大阪観光局IJD2014事務局<br>";

// Chữ ký trong Email
$sign2 = "大阪観光局IJD2014事務局"."\r\n";
$sign2.= "URL : http://ijd2014-osaka.jp/"."\r\n";
$sign2.= "MAIL : ".$to;

// Tên các trường trong form (từ trên xuống dưới)
$form = array("firstname","email","textarea");
// Tiêu đề của các trường trong form (phù hợp với thử tự trong mảng $FROM
$jpn = array("お名前","e-mail","お問い合わせ内容");
// Các trường bắt buộc (0 = không bắt buộc, 1 = bắt buộc phù hợp với mảng $form)
$error_1 = array(1,1,1);

// Xác định gửi Email（1 = gửi email, 0 = không gửi email）
$remail = 1;

// Không hiện thông báo liên quan tới việc tạo biến
error_reporting (E_ALL ^ E_NOTICE);

// ===========================================================================
// Cài đặt hiển thị
// ===========================================================================

// Hiển thị đầu vào
if((empty($_POST["submit_1"]))&&(empty($_POST["submit_2"]))) {
    $form_menu = "input";

    // Tạo biến cho các trường
    } else {
        $form_menu = "process";

        // Lấy giá trị các trường
        for($i=0; $i<count($form); $i++) {
            ${$form[$i]} = $_POST[$form[$i]];
        }
    }

// Xóa giá trị các trường
if(!empty($_POST["reset"])) {
    for($z=0; $z < sizeof($form); $z++) {
        ${$form[$z]} = "";
    }
}

// ===========================================================================
// Kiểm tra các trường
// ===========================================================================

if(!empty($_POST["submit_1"])) {

    // Trường bắt buộc phải nhập
    for($i=0; $i<count($form); $i++) {
        if($error_1[$i]==1) {
            if(empty(${$form[$i]})) {
                $errormsg.= "※ ".$jpn[$i]."を入力してください。<br>";
            }
        }
    }

    // Xác định giá trị Email đúng
    if(!empty($email)) {
        if(!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/",$email)) {
            $errormsg.= "※ e-mailの記入形式に誤りがあります。（全角で記入しているなど）<br>";
        }
    }
    if($mail != $mail2) {
        $errormsg.= "※ e-mailと確認用e-mailの内容が異なります。<br>";
    }

    // Tránh bị lỗi
    for($i=0; $i<count($form); $i++) {
        if(isset(${$form[$i]})) {
            ${$form[$i]} = htmlspecialchars(${$form[$i]},ENT_QUOTES); // check kỹ lại
            if(mb_ereg("[ｱ-ﾝ]", ${$form[$i]})) {
                ${$form[$i]} = mb_convert_kana(${$form[$i]},"KV","UTF-8");
            }
        }
    }

    if(empty($errormsg)) {
        $error="ok";
    } else {
        $error="re";
    }
}

// ===========================================================================
// Xử lý dữ liệu
// ===========================================================================
if(!empty($_POST["submit_2"])){
    if(empty($error)) {

        for($i=0; $i<count($form); $i++) {
            ${$form[$i]} = preg_replace("/<br>/","",${$form[$i]});
            ${$form[$i]} = preg_replace("/<br \/>/","",${$form[$i]});
            ${$form[$i]} = htmlspecialchars_decode(${$form[$i]},ENT_QUOTES);
        }

        //-------------------------------------------------
        // Thiết lập ngôn ngữ, mã hóa ký tự của dữ liệu
        //-------------------------------------------------
        mb_language("Japanese");
        mb_internal_encoding("UTF-8");

        // E-mail cho người quản trị
        $title = "観覧お申し込みフォームからの申し込み";
        $headers = "From:" . $mail;
        $msg1 = "ウェブサイトから下記の申し込みがありました。\r\n\r\n";
        $msg = "------------------------------------------\r\n\r\n";
        $msg.= "[お名前]　".$firstname."\r\n";
        $msg.= "[e-mail]　".$mail."\r\n";
        $msg.= "[その他特記事項]　\r\n".$textarea."\r\n\r\n";
        $admin_msg = $msg1.$msg;

        // E-mail cho người gửi
        if($remail==1) {
            $reto = $mail;
            $title2 = "チケット予約申し込みありがとうございます";
            $headers2 = "From:".$to;
            $msg2 = $firstname." 様\r\n\r\n";
            $msg2.= "この度は観覧のお申し込みを頂き誠にありがとうございます。\r\n";
            $msg2.= "本メールはお申し込みフォームを送信した方に自動配信しております。\r\n\r\n";
            $msg2.= "事務局より受付メールを送信致します。\r\n支援金のお振込は受付メール確認後にお願い致します。\r\n※正しい情報が送信されているか下記内容を今一度ご確認下さい。\r\n\r\n";
            $retrun_msg = $msg2.$msg.$sign2;
        }

        $form_menu = "end";

        // Mã hóa ký tự JIS
        $admin_msg = mb_convert_encoding($admin_msg,"JIS"); // SJIS JIS EUC-JP
        $retrun_msg = mb_convert_encoding($retrun_msg,"JIS");

        // Gửi thư
        if(mb_send_mail($to, $title, $admin_msg, $headers)) {
            if($remail==1) {
                mb_send_mail($reto, $title2, $retrun_msg, $headers2);
            }
            $form_menu = "end";
            $success_comment = "<div id='divloadform'>";
            $success_comment .= "<p>お問い合わせありがとうございました。</p>";
            $success_comment .= "</div>";
        } else {
            $success_comment = "<div id='divloadform'>";
            $success_comment .= "<p><font color='FF0000'>メールの送信に失敗しました。</font><br>";
            $success_comment .= "お手数ですが下記連絡先まで、お電話にてお問い合わせいただきますようお願いいたします。<br><br></p>";
            $success_comment .= "</div>";
        }
    }
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head><meta name="keywords" content="金沢市,石川県,介護施設,老人ホーム,運営コンサルタント,グランドケアコンサルタント," />
<meta name="description" content="金沢市（石川県）の介護福祉施設コンサルタント｜株式会社グランドケアコンサルタント｜お問い合わせ" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>株式会社グランドケアコンサルタント｜Grand Care Consultant Co., Ltd.｜お問い合わせ</title>
<link href="css/contact.css" rel="stylesheet" type="text/css" />
</head>

<body id="contact">
<div id="wrapper"> 
  <!--header-->
  <div id="header"> 
    <!--left_head-->
    <div class="left_head">
      <h1 class="sologan"><img src="img/common_img/sologan.jpg"   alt="民間活力による長寿福祉社会のまちづくり"/></h1>
      <div class="logo"><a href="index.html"><img src="img/common_img/logo.jpg" alt="Grand Care Consultant Co., Ltd.株式会社 グランドケアコンサルタント"/></a></div>
    </div>
    <!--//left_head-->
    <h2 class="middle _head"><img src="img/common_img/address.png" width="126" height="51"  alt="〒921-8013
石川県金沢市新神田
1-9-20"/></h2>
    <!--right_head-->
    <div class="right_head">
      <div class="tel"><img src="img/common_img/tel.jpg" width="386" height="36"  alt="電話でのお問い合わせはこちら076-292-0003"/></div>
      <div class="email"><a href="contact.php"><img src="img/common_img/email.jpg" width="386" height="36"  alt="メールでのお問い合わせはこちらgcc@sirius.ocn.jp"/></a></div>
    </div>
    <!--//right_head--> 
  </div>
  <!--//header--> 
  <!--warp-->
  <div id="warp">
    <div class="main_img"><img src="img/common_img/main_img.png"  alt="民間活力による長寿福祉社会のまちづくり
Grand Care Consultant Co., Ltd.
株式会社 グランドケアコンサルタント"/></div>
    <ul class="navi_content">
      <li><a href="index.html"><img src="img/navi_img/index_content.jpg" width="86" height="20"  alt=""/></a></li>
      <li class="b"><a href="business.html"><img src="img/navi_img/business_content.jpg" width="106" height="23"  alt=""/></a></li>
      <li class="active"><a href="contact.php"><img src="img/navi_img/contact_content.jpg" width="153" height="23"  alt=""/></a></li>
    </ul>
    <!--contents-->
    <div class="contents">
<h1><img src="img/contact_img/contact_h1.jpg" width="155" height="21"  alt=""/></h1>
<div id="form">
        <form action="contact.php" method="POST" id="forminfo" class="forminfo">
            <?php
            // ===========================================================================
            // Giao diện đầu vào
            // ===========================================================================
            if(($form_menu=="input")||($error=="re")) {
            // Thông báo lỗi
                if(!empty($errormsg)) {
                    $disp_error = "<br>".$errormsg;
                }
            ?>
                <div class="caution">
                    <?=$disp_error?>
                </div>
            <table class="tableinfo">
                <tr>
                    <td>
                        <label class="label">お名前</label>
                    </td>
                    <td>
                        <input type="text" id="firstname" name="firstname" value="<?=$firstname?>" size="50">
                    </td>
                </tr>
                <tr>
                    <td>
                        <label class="label">e-mail</label>
                    </td>
                    <td>
                        <input type="text" id="email" name="email" value="<?=$email?>" size="50">
                    </td>
                </tr>
                <tr>
                  <td>
                    <label class="label lbmessage">お問い合わせ内容</label>
                    </td>
                    <td>
                        <textarea name="textarea" cols="38" rows="12" id="message"><?=$textarea?></textarea>
                    </td>
                </tr>
                <tr>
                    <td>
                    </td>
                    <td>
                        <input id="btninfo" type="submit" name="submit_1">
                        <input id="reset" type="button"  value="">
                    </td>
                </tr>                
            </table>
            <?php
            // ===========================================================================
            // Giao diện xác nhận
            // ===========================================================================
            } elseif($form_menu=="process") {
            for($i=0; $i<sizeof($form); $i++) {
                echo "<input type='hidden' name='".$form[$i]."' value='".${$form[$i]}."'>";
            }
            ?>
            <table class="tablesubmit">
                <tr>
                    <td class="firsttd">
                        <label class="label">お名前</label>\
                    </td>
                    <td id="refirstname"><?=$firstname?>
                    </td>
                </tr>
                <tr>
                    <td class="firsttd">
                        <label class="label">e-mail</label>\
                    </td>
                    <td id="reemail"><?=$email?>
                    </td>
                </tr>
                <tr>
                    <td class="firsttd1">
                        <label class="label">お問い合わせ内容</label>
                    </td>
                    <td class="firsttd2">
                        <?=nl2br($textarea)?>
                    </td>
                </tr>
            </table>
            <div class="button">
                <input id="btnback" type="button" value="Back" onClick="history.back()">
                <input id="btnsubmit" name="submit_2" type="submit" value="Submit">
            </div>
            <?php
            }
            // ===========================================================================
            // Giao diện đã gửi email
            // ===========================================================================
            if($form_menu=="end") {
                ?>
                <p class="end_text"><?=$success_comment?></p>
            <?
            }
            // ===========================================================================
            ?>
        </form>
    </div>
    </div>
    <!--//contents--> 
  </div>
  <!--//warp--> 
  <!--footer-->
  <div id="footer">
    <ul class="navi_footer">
      <li><a href="contact.php"><img src="img/navi_img/contact_footer.jpg" width="83" height="14"  alt=""/></a></li>
      <li><a href="business.html"><img src="img/navi_img/business_footer.jpg" width="56" height="14"  alt=""/></a></li>
      <li><a href="index.html"><img src="img/navi_img/index_footer.jpg" width="36" height="12"  alt=""/></a></li>
    </ul>
  </div>
  <!--//footer--> 
</div>
</body>
</html>
