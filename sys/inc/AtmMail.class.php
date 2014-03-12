<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    AtmMail class                 |
| @copyright     ©Andrey Brykin 2010-2014      |
| @last mod.     2014/03/03                    |
|----------------------------------------------|
|											   |
| any partial or not partial extension         |
| CMS Fapos,without the consent of the         |
| author, is illegal                           |
|----------------------------------------------|
| Любое распространение                        |
| CMS Fapos или ее частей,                     |
| без согласия автора, является не законным    |
\---------------------------------------------*/


/**
 * Class AtmMail
 */
class AtmMail {

    public $templatePath;

    public $Viewer;

    private $headers;

    private $from;

    private $body = '';

    private $lastError = false;


    public function __construct($templatePath) {
        $this->templatePath = rtrim($templatePath, DS) . DS;
        $this->Viewer = new Fps_Viewer_Manager(new Fps_Viewer_Loader());
    }


    public function prepare($template, $from = null, $additional_headers = null) {
        $this->from = ($from !== null) ? Config::read('admin_email') : $from;

        if (!empty($template)) {
            if (!file_exists($this->templatePath . $template . '.html'))
                throw new Exception("Email template '$template' not found.");
            $this->body = file_get_contents($this->templatePath . $template . '.html');
        }

        // headers
        $this->headers = "From: ".$_SERVER['SERVER_NAME']." <" . $this->from . ">\n";
        $this->headers .= "Content-type: text/html; charset=\"utf-8\"\n";
        $this->headers .= "Content-Transfer-Encoding: 8bit\n";
        $this->headers .= "Return-path: <" . $this->from . ">\n";
        if (!empty($additional_headers)) $this->headers .= $additional_headers;
    }


    public function setBody($body) {
        $this->body = $body;
    }


	public function sendMail($to, $subject, $context = array()) {
		$context = array_merge($context, array(
            'to' => $to,
            'subject' => $subject,
            'site_title' => Config::read('site_title'),
            'site_url' => 'http://' . $_SERVER['SERVER_NAME'] . get_url('/'),
        ));

        try {
            $body = $this->Viewer->parseTemplate($this->body, $context);
            mail($to, $subject, $body, $this->headers);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        return true;
	}


    public function getError() {
        return $this->lastError;
    }
}

