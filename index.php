<?php
/*
CSDN Blog Spider
Author:ShanaMaid
Time:2016-9-12
QQ:416193699
GitHub:ShanaMaid
Email:uestczeng@email.com
*/

ini_set('max_execution_time', '0');//由于爬虫时间较长，不添加此语句会导致错误  Fatal error: Maximum execution time of 30 seconds ;设置为0无限制时间

//error_reporting(E_ALL ^ E_WARNING);//屏蔽警告

$username = "";//请输入你csdn的用户名
//如http://blog.csdn.net/shanamaid   即shanamaid


// 开始爬虫
function SpiderGo($username){
	$index = getWebContent("http://blog.csdn.net/".$username);
	if ($index == -1) {
		echo "This is wrong username";
		return 1;
	}
	
	$sumPage = getPageNumber($index);
	
	
	for ($i=1; $i <= $sumPage; $i++) { 
		$list = getArticleList($i,$username);
		//echo "第".$i."页抓取成功<br>";
		for ($j=0; $j < sizeof($list[0]); $j++) { 
		//	echo iconv("utf-8","gb2312",$list[0][$j]);
			$fp_puts = fopen("Result\\".$list[1][$j].".html","w");//生成html文件
			 fwrite($fp_puts,"<meta charset=\"utf-8\">"); //让字体显示中文
			 fwrite($fp_puts, getArticleContent($list[1][$j],$username));
			 fclose($fp_puts);
			echo  $list[0][$j]."        抓取完成<br>";
		}
	}
	
	
}


//加载网页内容
function getWebContent($url){
	$handle =fopen($url, "r");
	if ($handle) {
		return  stream_get_contents($handle,-1,-1);
	}
	else{
		return -1;
	}
	
}


//获取文章总页数
function getPageNumber($content){
	$tag = '/共[0-9]+页/';
	$tagNumber = '/[0-9]+/';
	preg_match($tag, $content,$result);
	preg_match($tagNumber, $result[0],$sumPage);
	return $sumPage[0];
}


// 获取文章内容
function getArticleContent($number,$username){
	$url = "http://blog.csdn.net/".$username."/article/details/".$number;
	$content = getWebContent($url);
	//echo $content;
	$tag = '/<div id="article_content" class="article_content">[\w\W]*<\/div>[\w\W]*<!-- B/'; //匹配正文内容
	preg_match($tag,$content,$main);//提出出文章题目编号
	return $main[0];
}



//下一页
function nextPage(){

}


//获取文章列表
function getArticleList($page,$username){
	//echo $content;
	$url = 'http://blog.csdn.net/'.$username.'/article/list/'.$page;
	$content = getWebContent($url);
	$tag = '/<span class="link_title">[\w\W]*?<\/span>/';
	$tag_name =  '/<\/?[^>]+>/';//去除html标签
	$tag_url  =   '/[0-9]{5,}/';//提取编号
	preg_match_all($tag, $content, $result);//提取出包含有文章题目和编号的内容
	
	
	for ($i=0; $i < sizeof($result[0]); $i++){
		preg_match($tag_url,$result[0][$i],$number);//提出出文章题目编号
		$result[0][$i]=preg_replace($tag_name,'',$result[0][$i]);//提取出文章题目
		$result[1][$i]=$number[0];
		
	}
	
	return $result;
}



//处理文章内容中的图片
function getImage(){

}


//下载图片
function downloadImg(){

}

function trimall($str)//删除空格
{
    $qian=array(" ","　","\t","\n","\r");
    $hou=array("","","","","");
    return str_replace($qian,$hou,$str); 
}



//getArticleList("1","shanamaid");
 SpiderGo($username);
//getArticleContent("52441330","shanamaid");
//getPageNumber();

?>