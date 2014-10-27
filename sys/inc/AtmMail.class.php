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

    private $bodyParts = array();

    private $body = '';

    private $lastError = false;


    public function __construct($templatePath) {
        $this->templatePath = rtrim($templatePath, DS) . DS;
        $this->Viewer = new Fps_Viewer_Manager(new Fps_Viewer_Loader());
    }


    public function prepare($template, $from = null, $additional_headers = null) {
        $this->from = ($from === null) ? Config::read('admin_email') : $from;

        if (!empty($template)) {
            if (preg_match('#^[\d\w_\-]+$#i', $template)) {
                if (!file_exists($this->templatePath . $template . '.html'))
                    throw new Exception("Email template '$template' not found.");
                $this->body = file_get_contents($this->templatePath . $template . '.html');
            } else {
                $this->body = $template;
            }
        }

        // headers
        $this->prepareHeaders($additional_headers);
    }


    public function prepareHeaders($additional_headers = '') {
        $boundary = md5(time());

        $headers = "MIME-Version: 1.0\n";
        $headers .= "From: ".$_SERVER['SERVER_NAME']." <" . $this->from . ">\n";
        $headers .= "Content-type: multipart/alternative; boundary=\"$boundary\"\n";
        $headers .= "Content-Transfer-Encoding: 8bit\n";
        $headers .= "Return-path: <" . $this->from . ">\n";
        if (!empty($additional_headers)) $headers .= $additional_headers;

        $this->headers = $headers;


        $body_parts = "--$boundary\n";
        $body_parts .= "Content-Type: text/plain; charset=utf-8\n";
        $this->bodyParts[] = $body_parts;

        $body_parts = "--$boundary\n";
        $body_parts .= "Content-Type: text/html; charset=utf-8\n";
        $this->bodyParts[] = $body_parts;
    }


    public function setBody($body) {
        $this->body = $body;
    }


	public function sendMail($to, $subject, $context = array()) {
		$context = array_merge($context, array(
            'to' => $to,
            'subject' => $subject,
            'site_title' => Config::read('site_title'),
            'site_url' => 'http://' . $_SERVER['SERVER_NAME'] . '/',
        ));

        try {
            $body = $this->Viewer->parseTemplate($this->body, $context);
            $body = $this->joinBodyParts($body);
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


    private function joinBodyParts($body) {
        $output_body = '';

		// Create two copies of the message - plain text & HTML for different clients.
        if (count($this->bodyParts) > 0) {
			$output_body .= $this->bodyParts[0] . $this->getPlainTextFromHTML($body) . "\n";
			$output_body .= $this->bodyParts[1] . $this->getCleanHTML($body) . "\n";
        }

        return $output_body;
    }

    private function getCleanHTML($body) {
        $body = preg_replace('#(\n)+#ium', "\n\r", $body);
        return $body;
    }
	
	
    private function getPlainTextFromHTML($body) {
        $body = preg_replace('#\<a[^>]*href="([^"]+)"[^>]*\>([^<]+)\</a>#ium', '$2 - $1', $body);
        $body = preg_replace('#(\<br[^>]*\>)#ium', "\n\r", $body);
        $body = preg_replace('#(\n)+#ium', "\n\r", $body);
        return strip_tags($body);
    }
}

