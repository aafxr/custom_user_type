<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
  "NAME" => GetMessage("NIKOLAEVEVGE_DATE_CURRENT_DESCRIPTION_NAME"),
  "DESCRIPTION" => GetMessage("NIKOLAEVEVGE_DATE_CURRENT_DESCRIPTION_TEXT"),
  "PATH" => array(
    "ID" => "nikolaevevge_components",
    "NAME" => GetMessage("NIKOLAEVEVGE_DATE_CURRENT_DESCRIPTION_GROUP_NAME"),
    "CHILD" => array(
      "ID" => "curdate",
      "NAME" => GetMessage("NIKOLAEVEVGE_DATE_CURRENT_DESCRIPTION_CHILD_NAME")
    )
  ),
//  "ICON" => "/images/icon.gif",
);
