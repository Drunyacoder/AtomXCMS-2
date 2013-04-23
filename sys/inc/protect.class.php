<?php


class Protect
{
    public function checkIpBan()
    {
        if (file_exists(ROOT . '/sys/logs/ip_ban/baned.dat')) {
            $data = file(ROOT . '/sys/logs/ip_ban/baned.dat');

            if (!empty($_SERVER['REMOTE_ADDR'])) {
                $ip = trim(substr($_SERVER['REMOTE_ADDR'], 0, 15));
                if (in_array($ip, $data)) {
                    redirect('/error.php?ac=ban');
                }
            }

            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = trim(substr($_SERVER['HTTP_X_FORWARDED_FOR'], 0, 15));
                if (in_array($ip, $data)) {
                    redirect('/error.php?ac=ban');
                }
            }

        }
    }



    public function antiDdos()
    {
        touchDir(ROOT . '/sys/logs/anti_ddos/');
        $date = date("Y-m-d");

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '';
        }
        if (mb_strlen($ip) > 15
            || !preg_match('#^\d+\.\d+\.\d+\.\d+$#', $ip)
            || empty($ip))
            return;


        if (!empty($ip)) {
            /* if current IP is hacked */
            if (file_exists(ROOT . '/sys/logs/anti_ddos/hack_' . $ip . '.dat')) {
                redirect('/error.php?ac=hack');
            }

            //clean old files
            $tmp_files = glob(ROOT . '/sys/logs/anti_ddos/[0-9]*.dat'); //get all except HACK
            if (!empty($tmp_files) && count($tmp_files) > 0) {
                foreach ($tmp_files as $file) {
                    if (substr(basename($file), 0, 10) != $date) {
                        unlink($file);
                    }
                }
            }

            /* if not hacked */
            $file = ROOT . '/sys/logs/anti_ddos/' . $date . '_' . $ip . '.dat';
            if (file_exists($file)) {
                $data = file_get_contents($file);
                $data = explode('***', $data);
                if ($data[1] == time()) {
                    if ($data[0] > Config::read('request_per_second', 'secure')) {
                        unlink($file);
                        $f = fopen(ROOT . '/sys/logs/anti_ddos/hack_' . $ip . '.dat', 'w');
                        fwrite($f, date("Y-m-d H:i"));
                        fclose($f);
                        redirect('/error.php?ac=hack');
                    }
                    $attempt = $data[0] + 1;
                    $f = fopen($file, 'w');
                    fwrite($f, $attempt . '***' . time());
                    fclose($f);
                } else {
                    unlink($file);
                }
            } else {
                $f = fopen(ROOT . '/sys/logs/anti_ddos/' . $date . '_' . $ip . '.dat', 'w');
                fwrite($f, '1***' . time());
                fclose($f);
            }
        }
    }



    public function antiSQL()
    {
        if(!preg_match('#^[\#/\?&_\-=\.а-яa-z0-9]*$#ui', urldecode($_SERVER['REQUEST_URI']))) {

            $remote_addr = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : 'Unknown';
            $http_x_for = (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : 'Unknown';
            $http_client_ip = (!empty($_SERVER['HTTP_CLIENT_IP'])) ? $_SERVER['HTTP_CLIENT_IP'] : 'Unknown';

            $remote_addr = substr($remote_addr, 0, 150);
            $http_x_for = substr($http_x_for, 0, 150);
            $http_client_ip = substr($http_client_ip, 0, 150);
            $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, 500);

            $logfile = fopen(ROOT . '/sys/logs/antisql.dat', 'a');
            $warning = "Попытка SQL-иньекции. ['REMOTE_ADDR'] -> " . $remote_addr . " Дата: " . date("Y-m-d H:i") . "
                ['HTTP_X_FORWARDED_FOR'] -> " . $http_x_for . "
                ['HTTP_CLIENT_IP'] -> " . $http_client_ip . "
                Запрос: " . urldecode($_SERVER['REQUEST_URI']) . "\n";
            fputs($logfile, $warning);
            fclose($logfile);

            redirect('/');
        }
    }
}