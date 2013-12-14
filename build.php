<?php
if ($argc != 2) {
	echo "必须指定版本号";
	exit;
}

$version=$argv[1];

echo <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8" />
<title>MiniCMS安装程序</title>
<style>
* {padding:0;margin:0;font-family:"Microsoft YaHei",Segoe UI,Tahoma,Arial,Verdana,sans-serif;}
html,body { height:100%; }
body {background:#f9f9f9; font-size:14px;}
#main {position:absolute; left:50%; top:50%;}
#mainbox {background:#fff;border:1px solid #ccc; padding:20px; -webkit-border-radius:10px; -moz-border-radius:10px; border-radius:10px; margin-bottom:20px; }
label { font-weight:bold; color:#333; font-size:12px; }
.textbox input { border:none; padding:0; font-size:18px; width:312px; color:#333; outline:0; }
.textbox { border:1px solid #e0e0e0; padding:6px; margin:6px 0 20px; border-radius:3px 3px 3px 3px; }
</style>
<script type="text/javascript">
function vMiddle(inner){
	var outer = (inner.parentNode.tagName == 'body') ?  document.documentElement : inner.parentNode;

	var innerHeight = inner.offsetHeight,
		innerWidth = inner.offsetWidth,
		outerHeight = outer.offsetHeight ,
		outerWidth = outer.offsetWidth ;

	(outerHeight > innerHeight) ? (function(){
		inner.style.marginTop = -innerHeight/2 + "px" ;
		inner.style.top = "50%";
	})()
	: (function(){
		inner.style.marginTop = 0;
		inner.style.top = 0;
	})();

	(outerWidth > innerWidth) ?  (function(){
		inner.style.marginLeft = -innerWidth/2 + "px" ;
		inner.style.left = "50%";
	})()
	: (function(){
		inner.style.marginLeft = 0;
		inner.style.left = 0;
	})();
}

window.onload = window.onresize = function(){
	vMiddle(document.getElementById("main"));
}
</script>
</head>
<body style="background:#f2f2f2;">
  <div id="main">
    <div style="font-size:32px;font-weight:bold;text-align:center;padding-top:40px;">MiniCMS安装程序</div>
    <div style="font-size:13px;color:#888;text-align:center;padding:10px 0 20px;">v{$version}</div>
    <div id="mainbox">
<?php if (!isset(\$_POST["start_install"])) { ?>
    <form method="post" action="<?php echo \$_SERVER['PHP_SELF']; ?>">
<?php if (!is_file('mc-files/mc-conf.php')) { ?>
      <label>网站标题</label>
      <div class="textbox"><input type="text" name="sitename" value="我的网站"/></div>
      <label>您的昵称</label>
      <div class="textbox"><input type="text" name="nickname" value="神秘人"/></div>
      <label>后台账号</label>
      <div class="textbox"><input type="text" name="username" value="admin"/></div>
      <label>后台密码</label>
      <div class="textbox"><input type="text" name="password" value="123456"/></div>
      <div style="text-align:center;"><input type="submit" name="start_install" value="开始安装" style="border:1px solid #ccc;background:#efefef;padding:8px 10px;font-size:16px;cursor:pointer;"/></div>
<?php } else { ?>
      <div style="text-align:center;padding-bottom:20px;">检测到MiniCMS配置文件，将使用升级模式安装。</div>
      <div style="text-align:center;"><input type="submit" name="start_install" value="开始升级" style="border:1px solid #ccc;background:#efefef;padding:8px 10px;font-size:16px;cursor:pointer;"/></div>
<?php } ?>
    <form>
<?php } ?>
<?php
      \$install_failed=false;

      function install(\$file, \$content) {
        global \$install_failed;

        if (\$install_failed)
        	return;

        echo "解压 \$file";

        \$dir = dirname(\$file);

        if (is_dir(\$dir) || @mkdir(\$dir, 0744, true)) {
          if (!@file_put_contents(\$file, gzuncompress(base64_decode(\$content)))) {
            \$install_failed = true;
            echo '[<span style="color:red;">失败</span>]';
          }
        } else {
            \$install_failed = true;
            echo '[<span style="color:red;">无法创建目录</span>]';
        }
        echo '<br/>';
      }

      if (isset(\$_POST["start_install"])) {
      ?>
        <style> #main { min-width:400px; } </style>
        <div style="font-size:13px;color:#666;line-height:16px;padding:20px;margin:0 auto;">
<?php
HTML;

$dirs = array(
  ".",
);

$ignores = array(
  'README.md',
  'build.php',
  'install.php',
);

build($dirs);

echo <<<HTML
    \$is_upgrade=true;

    if (!\$install_failed) {
      if (!is_file('mc-files/mc-conf.php')) {
        \$is_upgrade = false;
        echo '<br/>';
        echo "创建配置文件";
        if (!@file_put_contents('mc-files/mc-conf.php', 
        	"<?php \\\$mc_config = array(".
        	"'version' => '\$version',".
        	"'site_link' => '',".
        	"'site_name' => '{\$_POST['sitename']}',".
        	"'site_desc' => '又一个MiniCMS网站',".
        	"'user_name' => '{\$_POST['username']}',".
        	"'user_pass' => '{\$_POST['password']}',".
        	"'user_nick' => '{\$_POST['nickname']}',".
        	"'comment_code' => '');?>"
        )) {
          \$install_failed = true;
          echo '[<span style="color:red;">失败</span>]';
        }
        echo '<br/>';
      }

      if (!is_dir('mc-files/posts/index')) {
        echo '<br/>';
        echo "创建文章索引目录";
        if (@mkdir('mc-files/posts/index', 0744, true)) {
          echo '<br/>';
          echo "创建页面索引文件";
          if (
            !@file_put_contents('mc-files/posts/index/delete.php', '<?php \$mc_posts=array(); ?>') ||
            !@file_put_contents('mc-files/posts/index/publish.php', '<?php \$mc_posts=array(); ?>') ||
            !@file_put_contents('mc-files/posts/index/draft.php', '<?php \$mc_posts=array(); ?>')
          ) {
            \$install_failed = true;
            echo '[<span style="color:red;">失败</span>]';
            rmdir('mc-files/posts/index');
          }
          echo '<br/>';
        } else {
          \$install_failed = true;
          echo '[<span style="color:red;">失败</span>]';
          echo '<br/>';
        }
      }

      if (!is_dir('mc-files/pages/index')) {
        echo '<br/>';
        echo "创建页面索引目录";
        if (@mkdir('mc-files/pages/index', 0744, true)) {
          echo '<br/>';
          echo "创建页面索引文件";
          if (
            !@file_put_contents('mc-files/pages/index/delete.php', '<?php \$mc_pages=array(); ?>') ||
            !@file_put_contents('mc-files/pages/index/publish.php', '<?php \$mc_pages=array(); ?>') ||
            !@file_put_contents('mc-files/pages/index/draft.php', '<?php \$mc_pages=array(); ?>')
          ) {
            \$install_failed = true;
            echo '[<span style="color:red;">失败</span>]';
            rmdir('mc-files/pages/index');
          }
          echo '<br/>';
        } else {
          \$install_failed = true;
          echo '[<span style="color:red;">失败</span>]';
          echo '<br/>';
        }
      }
    }
?>
    </div>
<?php if (\$install_failed) { ?>
    <div style="text-align:center;"><?php if (\$is_upgrade) { ?>升级<?php } else { ?>安装<?php } ?>失败</div>
<?php } else { ?>
    <div style="text-align:center;"><?php if (\$is_upgrade) { ?>升级<?php } else { ?>安装<?php } ?>完毕</div>
<?php if (!unlink(__FILE__)) { ?>
     <div style=\"text-align:center;padding-top:20px;color:red;\">安装文件无法删除，请手工删除。</div>
<?php } ?>
    <div style="text-align:center;padding:20px 0 0;">
    <form method="get" action="/mc-admin">
    <input type="submit" value="开始体验" style="border:1px solid #ccc;background:#efefef;padding:8px 10px;font-size:16px;cursor:pointer;"/>
    </form>
    </div>
<?php } ?>
<?php } ?>
    </div>
  </div>
</body>
</html>
HTML;

function build($dirs) {
  global $ignores;

	foreach ($dirs as $dir) {
		if (!is_dir($dir)) {
			echo "目录\"$dir\"不存在";
			exit;
		}

		if ($dh = opendir($dir)) {
			$sub_dirs = array();
			while (($item = readdir($dh)) !== false) {
				if ($item[0] == '.')
					continue;

        if ($dir == '.')
          $file = $item;
        else
				  $file = $dir."/".$item;

        if (in_array($file, $ignores))
          continue;

				if (is_dir($file)) {
					$sub_dirs[] = $file;
				} else {
					echo "      install('$file', <<<DATA\n";
					echo base64_encode(gzcompress(file_get_contents($file)));
					echo "\nDATA\n);\n";
				}
			}
			closedir($dh);
			build($sub_dirs);
		} else {
			echo "目录\"$dir\"无法访问";
			exit;
		}
	}
}

