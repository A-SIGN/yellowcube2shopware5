<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; utf-8">
<meta name="author" content=""/>
<meta name="copyright" content="" />

<title></title>
<style type="text/css">
body {
	width:100%;
	font-family:Verdana,Arial,Helvetica,sans-serif;
	font-size:11px
}
div#head_logo {
	height:20mm;
	width:90mm;
	margin-bottom:5mm;
	margin-top: 200mm;
}
div#head_sender {
}
div#header {
	height:60mm;
}
div#head_left {
	width:120mm;
	height:60mm;
	float:left
}
div#head_right {
	width:45mm;
	height:60mm;
	float:left;
	margin-top:-20px;
	margin-left:5px
}
div#head_bottom {
	font-size:14px;
	height:10mm
}
div#content {
	height:65mm;
	width:170mm
}
td {
	white-space:nowrap;
	padding:5px 0
}
td.name {
	white-space:normal
}
td.line {
	border-bottom:1px solid #999;
	height:0px
}
td.head {
	border-bottom:1px solid #000
}
#footer {
	width:170mm;
	position:fixed;
	bottom:-20mm;
	height:15mm
}
#amount {
	margin-left: 90mm
}
#sender {
	{$Containers.Header_Sender.style}
}

#info {
	{$Containers.Content_Info.style}
}

/** CN22 page changes **/
.main-container {
	margin-top: 200mm;
}
img.signpic {
	margin-top: 0mm;
	width: 20%;
	margin-bottom: 10mm;
}
.main-table {
	font-size: 8px;
}
.main-table td {
	padding-top: 0;
	padding-bottom: 0;
}
.line-bottom {
	border-bottom: 1px solid #000;
}
.line-right {
	border-right: 1px solid #000;
}
.line-left {
	border-left: 1px solid #000;
}
.line-top {
	border-top: 1px solid #000;
}
.thead td {
	font-weight: normal;
	text-align: left;
}
.article-table {
	width: 100%;
}
.article-table .thead td {
	padding: 5px;
}
.tdadd {
	font-size: 12px;
	padding-left:10px;
}
td {
	line-height: 20px;
}
h1 {
	margin: 0;
}
.marlft-110 {
	margin-left: 110px;
}
.padd-none {
	padding: 0;
}
.paddlr-5 {
	padding-left: 5px;
	padding-right: 5px;
}
</style>

<body>
{* additional information for International Handling *}
{if $User.shipping.country}
	{assign var="billedcountry" value=$User.shipping.country->id|intval}
{else}
	{assign var="billedcountry" value=$User.billing.country->id|intval}
{/if}

{assign var=isForeignCountry value=false}
{if $billedcountry != $smarty.session.Shopware.shopLocaleId}
	{assign var=isForeignCountry value=true}
{/if}

<div class="main-container">
	{* start 3-time printer loop *}
	{assign var="includefile" value=$smarty.current_dir|cat:'/ycubecontent.tpl'}
	{if $isForeignCountry}
		{* include CN22 section *}
		{* SPS_INTPRI = 112; SPS_INTECO = 113 *}
		{if $Order._order.dispatchID == "112" || $Order._order.dispatchID == "113"}
			{include file=$smarty.current_dir|cat:'/cn22content.tpl' Pages=$Pages}
		{/if}
		{section name=foo start=0 loop=3 step=1}
			{include file=$includefile Pages=$Pages}
		{/section}
	{else}
		{include file=$includefile Pages=$Pages}
	{/if}
</div>
</body>
</html>
