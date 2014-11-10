<?php
/**
 * Upgrade Functonality
 * @author Justin Greer justin@justin-greer.com
 * @package WordPress OAuth Server
 */
function wo_verifiy_license ($license=null)
{
  $site_url=site_url(null, "http");
  $simple_key="wpoauth";

  return md5($site_url.$simple_key) == $license ? true:false;
}

/**
 * [generate_license description]
 * @return [type] [description]
 */
function generate_license ()
{
  $site_url=site_url(null, "http");
  $simple_key="wpoauth";

  return md5($site_url.$simple_key);
}