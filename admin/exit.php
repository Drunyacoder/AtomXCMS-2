<?php
/*-----------------------------------------------\
| 												 |
| @Author:       Andrey Brykin (Drunya)          |
| @Email:        drunyacoder@gmail.com           |
| @Site:         http://atomx.net                |
| @Version:      1.0                             |
| @Project:      CMS                             |
| @package       CMS AtomX                       |
| @subpackege    Admin Panel module  			 |
| @copyright     �Andrey Brykin 2010-2013        |
\-----------------------------------------------*/

/*-----------------------------------------------\
| 												 |
|  any partial or not partial extension          |
|  CMS AtomX,without the consent of the          |
|  author, is illegal                            |
|------------------------------------------------|
|  ����� ���������������                         |
|  CMS Fapos ��� �� ������,                      |
|  ��� �������� ������, �������� �� ��������     |
\-----------------------------------------------*/


include_once '../sys/boot.php';
include_once ROOT . '/admin/inc/adm_boot.php';


if (isset($_SESSION['adm_panel_authorize'])) unset($_SESSION['adm_panel_authorize']);
redirect('/');


