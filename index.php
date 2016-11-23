<?php
/*
CSDN Blog Spider
Author:ShanaMaid
Time:2016-9-12
QQ:416193699
GitHub:ShanaMaid
Email:uestczeng@email.com
*/


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
		echo "第".$i."页抓取成功<br>";
		for ($j=0; $j < sizeof($list[0]); $j++) { 
			$fp_puts = fopen("Result\\".$list[1][$j].".html","w");//生成html文件
			$content = getArticleContent($list[1][$j],$username);
			$imgUrl  = getImage($content); //图片链接
			$imgName = initName(sizeof($imgUrl),$list[1][$j]); //生成图片数组的名字
			$imglocalUrl = initUrl(sizeof($imgUrl),$list[1][$j]); //生成图片本地url
			$content_replace_url = replaceImgUrl($content,$imglocalUrl);//替换图片链接为本地
			downloadImg($imgUrl,$list[1][$j],$imgName);
			  fwrite($fp_puts,"<meta charset=\"utf-8\">"); //让字体显示中文
			  fwrite($fp_puts,  $content_replace_url);
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
	$tag = '/<div id="article_content" class="article_content">[\w\W]*<\/div>[\w\W]*<!-- B/'; //匹配正文内容
	preg_match($tag,$content,$main);//提出正文内容
	return $main[0];
}



//获取文章列表
function getArticleList($page,$username){
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



//提取-文章内容中的图片
function getImage($content){
	$tag = '/http:\/\/img.blog.csdn.net\/.*?\/Center/'; 
	preg_match_all($tag, $content, $result);//筛选出图片
	return $result[0];//图片链接数组
}


//替换-文章内容中的图片链接为本地
function replaceImgUrl($content,$local_url){
	$tag = '/http:\/\/img.blog.csdn.net\/.*?\/Center/'; 
	$tag_array = array();
	for ($i=0; $i < sizeof($local_url) ; $i++) { 
		$tag_array[$i] = $tag;
	}
	$count = 1;
	$st = preg_replace($tag_array, $local_url, $content,$count);
	return $st;

}

//下载图片
function downloadImg($url,$id,$imgName){
	if(!is_dir("Img\\".$id))
	{
	  mkdir("Img\\".$id);
	}
	for ($i=0; $i <sizeof($url) ; $i++) { 
		$curl = curl_init();
		curl_setopt($curl,CURLOPT_URL, $url[$i]);
		curl_setopt ($curl, CURLOPT_HEADER, false);
		curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($curl);
		curl_close($curl);
		file_put_contents($imgName[$i],$result);
	}
}

function trimall($str){
    $qian=array(" ","　","\t","\n","\r");
    $hou=array("","","","","");
    return str_replace($qian,$hou,$str); 
}

//初始化图片名字
function initName($length,$id){
	$result = array();
	for ($i=0; $i < $length; $i++) { 
		$result[$i] = 'Img\\'.$id.'\\'.$i.'.png'; 
	}
	return $result;
}

//初始化本地路径
function initUrl($length,$id){
	$result = array();
	for ($i=0; $i < $length; $i++) { 
		$result[$i] = "..\\Img\\\\\${1}".$id."\\\\\${1}".$i.'.png'; 
	}
	return $result;
}


SpiderGo($username);

?>
