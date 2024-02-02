Catpow SSG
===

<img src="https://img.shields.io/badge/PHP-8.0-45A?logo=php"> 

PHPのビルドインサーバーを利用したシンプルな静的サイト生成ツール。  
軽量で、サイト内の特設ページやデータ納品のLPの作成などに手軽に導入できます。

サイトのディレクトリに移動し

 ```command
git clone --recursive https://github.com/cpprj/catpow-ssg.git _compiler
 ```
 または、サブモジュールとして

 ```command
git submodule add https://github.com/cpprj/catpow-ssg.git _compiler
 ```
 
でインストール

 ```command
php _compiler/server.php
 ```
 
でサーバーを起動します。

概要
--

CLIでserver.phpを実行することで親ディレクトリをルートディレクトリ、server.phpをルーターとするPHPのビルドインサーバーが実行され、cssがリクエストされた場合は対応するscssが存在する場合はそれをコンパイルし、xml,htmlがリクエストされた場合は対応するテンプレートがあればその出力でファイルを上書きしてからファイルを出力します。その他、画像やjsファイルがリクエストされ、そのファイルが存在しない場合は、テンプレートディレクトリ内に同名のファイルがあればそれをコピーします。


環境
--

CLIでphp8が実行できる環境が必要です。

### Macの場合

```command
php -v
```

でPHPバージョンを確認し、8.0未満の場合、あるいはインストールされていない場合は以下の手順でPHPをインストールしてください。

#### 1. Homebrewをインストール

パッケージ管理ツール[Homebrew](https://brew.sh/index_ja)をインストールします。ターミナルで以下のコマンドを実行してください。

```command
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

このインストールには10分程度を要します。すでにインストールされている場合は不要です。

#### 2. phpをインストール

HomebrewからPHPをインストールします。ターミナルで以下のコマンドを実行してください。

```command
brew install php
```

インストールが完了したら

```command
php -v
```

でPHPバージョンを確認し、8.0以上のPHPがインストールされたことを確認します。

アクセス権限などの問題でインストールに失敗する場合があります。インストール実行後のメッセージを確認して適宜対応してください。

テンプレート
---

対応するテンプレートが存在するxml,htmlのファイルがリクエストされると、対応するテンプレートのphpの出力でリクエストされたファイルが上書きされます。

 ``[ディレクトリ]/[ファイル名].html``がリクエストされた場合は  
``[ディレクトリ]/[ファイル名].html.tmpl.php``  
``_tmpl/[ディレクトリ]/[ファイル名].html.php``  
のいずれかがテンプレートとなります。



PHPのクラスのオートロード
---

CatpowSSGはPHPのクラスのオートロードの仕組みを備えています。

テンプレートの読み込みの前にオートロードの処理が登録されるので、xml,htmlのテンプレートファイルでは依存するクラスのファイルをincludeすることなくクラスを使うことができます。

CatpowSSGのオートロードはクラスが見つからない場合、  
``../_config/classes/[名前空間]/[クラス名].php``  
``inc/classes/[名前空間]/[クラス名].php``  
のいずれかを見つけてincludeします。

SCSS
---

拡張子が``.css``のファイルをリクエストされると、同フォルダ内の``_scss``もしくは``_tmpl``フォルダ内の対応するフォルダ内の``_scss``の対応する``.scss``のファイルを見つけ、scssがcssより新しければ、scssをコンパイルしてcssを上書きします。

コンパイルは[scssphp](https://scssphp.github.io/scssphp/)を使ってPHPで行います。

``[ディレクトリ]/css/[ファイル名].css``がリクエストされた場合は  
``[ディレクトリ]/_scss/[ファイル名].scss``  
``_tmpl/[ディレクトリ]/_scss/[ファイル名].scss``  
のいずれかがコンパイル元のscssとなります。

``../``  
``../_scss/``  
``../_config/``  
``inc/scss/``  
がimport pathとして設定されます。

``inc/scss/``にはサブジュールとして[catpow-scss](https://github.com/synchrovision/cawpow-scss)が読み込まれています。

catpow-scssの各mixin、functionを利用するには``$colors,$fonts,$breakpoints``などのグローバル変数が定義されている必要があります。


CSV
---

CatpowSSGはCSVファイルを読み込んで利用するためのクラスと関数を備えています。

読み込んだCSVは一行目の値をキー値として二行目以降を連想配列とすることを基本にします。

以下のようなCSVのデータを読み込んだ場合

```csv
A,B,C
1,2,3
4,5,6
```

``select``メソッドで得られる配列は以下のようになります

```php
[
	['A'=>'1','B'=>'2','C'=>'3'],
	['A'=>'4','B'=>'5','C'=>'6']
];
```


CatpowのCSVクラスは単純に指定したCSVファイルを読み込んで一行ずつ処理する他に、任意のフォルダ内のcsvファイルをまとめて読み込んで配列に読み込む、条件に一致する行のみを抽出する、任意の列をキー値としてツリー構造のデータを得るといったことができます。 

関数csvにcsvフォルダ内のcsvのファイル名（拡張子省略）を引数として渡すことで、当該ファイルのCSVクラスのインスタンスを得ることができます。

CSVクラスはArrayAccessおよびIteratorを実装しており、配列のように扱うこともできます。

```php
<ul class="items">
<?php foreach(csv('items') as $row): ?>
	<li class="item">
		<h3 class="title"><?=$row['title']?></h3>
		<p class="text"><?=$row['text']?></p>
	</li>
<?php endforeach; ?>
</ul>
```


BEM
---

CatpowSSGはBEMによるコーディングを補助するためのクラスを備えています。

-で終わるクラスをセクション
_で終わるクラスをブロック  
_で始まるクラスをエレメント  
_を区切りとしてモディファイア  
としてクラスを置き換えます

各クラス名を出力したBEMインスタンスからは``export_selectors_file``メソッドでセレクタをまとめたSCSSファイルを書き出すことができます。

``_tmpl/sample.html.php``を以下のように記述した場合

```php
<?php
namespace Catpow;
$s=BEM::section('myLP');
ob_start();
?>
<section class="sec1-">
	<div class="myBlock_color1_">
		<ul class="_list">
			<li class="_item">
				<div class="_title">title1</div>
				<div class="_text">text1</div>
			</li>
			<li class="_item">
				<div class="_title">title2</div>
				<div class="_text">text2</div>
			</li>
			<li class="_item">
				<div class="_title">title3</div>
				<div class="_text">text3</div>
			</li>
		</ul>
	</div>
</section>
<?=$s->apply(ob_get_clean())?>
<?php $s->export_selectors_file(); ?>
```

出力される``sample.html``は以下のようになります。

```html
<section class="myLP-sec1">
	<div class="myLP-sec1-myBlock myLP-sec1-myBlock_color1">
		<ul class="myLP-sec1-myBlock__list myLP-sec1-myBlock_color1__list">
			<li class="myLP-sec1-myBlock__list__item myLP-sec1-myBlock_color1__list__item">
				<div class="myLP-sec1-myBlock__list__item__title myLP-sec1-myBlock_color1__list__item__title">title1</div>
				<div class="myLP-sec1-myBlock__list__item__text myLP-sec1-myBlock_color1__list__item__text">text1</div>
			</li>
			<li class="myLP-sec1-myBlock__list__item myLP-sec1-myBlock_color1__list__item">
				<div class="myLP-sec1-myBlock__list__item__title myLP-sec1-myBlock_color1__list__item__title">title2</div>
				<div class="myLP-sec1-myBlock__list__item__text myLP-sec1-myBlock_color1__list__item__text">text2</div>
			</li>
			<li class="myLP-sec1-myBlock__list__item myLP-sec1-myBlock_color1__list__item">
				<div class="myLP-sec1-myBlock__list__item__title myLP-sec1-myBlock_color1__list__item__title">title3</div>
				<div class="myLP-sec1-myBlock__list__item__text myLP-sec1-myBlock_color1__list__item__text">text3</div>
			</li>
		</ul>
	</div>
</section>
```

テンプレートの最終行にある``<?php $s->export_selectors_file(); ?>``で``_tmpl/_scss/selectors.scss``に書き出されるファイルは以下のようになります。

```scss：selectors.scss
.myLP{
	&-sec1{
		&-myBlock{
			&__list{
				&__item{
					&__text{
					}
					&__title{
					}
				}
			}
			&_color1{
				&__list{
					&__item{
						&__text{
						}
						&__title{
						}
					}
				}
			}
		}
	}
}
```