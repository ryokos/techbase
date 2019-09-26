<html>
<body>
<meta charset="utf=8">

<!--
5.制作実践！DBを組み合わせた掲示板を作れ！
-->

<?php

//---データベースに接続する（mission4-1コピペ）
//---dsn:データソースネーム
//---pdo:PHP Data Object
	$dsn = 'mysql:dbname="データベース名";host=localhost';
	$user = 'ユーザー名';
	$password = 'パスワード';
	$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

//---データベースにテーブル作成（mission4-2改造）
//---テーブル名は"mission5" passも表示はさせないけど必要
	$sql = "CREATE TABLE IF NOT EXISTS mission5"
	." ("
	. "id INT AUTO_INCREMENT PRIMARY KEY,"
	. "name VARCHAR(32) NOT NULL,"
	. "comment TEXT NOT NULL,"
	. "datetime VARCHAR(32) NOT NULL,"
	. "pass VARCHAR(32) NOT NULL"
	.");";
	$stmt = $pdo->query($sql);


$editname = ""; //編集番号が入力されていないときもフォーム内で出力されるから
$editcomment = ""; //↑に同じ
$editnum = "";//↑に同じ
$editpass = "";//↑に同じ


//---新規投稿がPOSTされたとき（もしどれか空だったら動かない）
if(!empty($_POST["user_name"])){
if(!empty($_POST["user_comment"])){
if(!empty($_POST["pass"])){


//---編集と新規投稿の区別
//---編集のとき（＝編集番号が入っていて、passも合っているとき）
if(!empty($_POST["com_num"])){
if($_POST["pass"] == $_POST["com_pass"]){

//	echo "編集モードだよ！<br>";//for check


	//---投稿内容の編集（mission4-7改造）
	//bindParamの引数（:nameなど）は4-2でどんな名前のカラムを設定したかで変える必要がある。
	$com_num = $_POST["com_num"];//変更する投稿番号
	$name = $_POST["user_name"];//変更したい名前
	$comment = $_POST["user_comment"];//変更したいコメント
	$datetime = date("Y/m/d H:i:s");//変更したときの日時

	$sql = 'update mission5 set name=:name,comment=:comment,datetime=:datetime where id=:id';
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':name', $name, PDO::PARAM_STR);
	$stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
	$stmt->bindParam(':datetime', $datetime, PDO::PARAM_STR);
	$stmt->bindParam(':id', $com_num, PDO::PARAM_INT);
	$stmt->execute();


//↑編集のとき
}
}else{

//↓新規投稿のとき

//	echo "新規投稿モードだよ！<br>";//for check

	//---データベースに新しく書き込む（mission4-5改造）

	$name = $_POST["user_name"];
	$comment = $_POST["user_comment"];
	$datetime = date("Y/m/d H:i:s");
	$pass = $_POST["pass"];

	$sql = $pdo -> prepare("INSERT INTO mission5 (name, comment,datetime,pass) VALUES (:name, :comment, :datetime, :pass)");
	$sql -> bindParam(':name', $name, PDO::PARAM_STR);
	$sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
	$sql -> bindParam(':datetime', $datetime, PDO::PARAM_STR);
	$sql -> bindParam(':pass', $pass, PDO::PARAM_STR);

	$sql -> execute();


}

}
}
}

	//---↑新規投稿がPOSTされたとき
	//---↓削除番号がPOSTされたとき


//---削除番号がPOSTされたとき
if(isset($_POST["delnum"])){
if(!empty($_POST["delpass"])){ //ひとまず空だったら動かないように

	$delnum = $_POST["delnum"];
	$delpass = $_POST["delpass"];

	//---データを取得する
	$sql = 'SELECT * FROM mission5 ';
	$stmt = $pdo->query($sql);
//	$results = $stmt->fetchAll();
	foreach ($stmt as $row){

		//---idとpassが合っていたら投稿を削除する（mission4-8改造）

		if($row['id'] == $delnum){
		if($row['pass'] == $delpass){

		$sql = 'delete from mission5 where id=:id';
		$stmt = $pdo->prepare($sql);
		$param = array(":id"=>$delnum);
//		$stmt->bindParam(':id', $id, PDO::PARAM_INT);//これ何？
		$stmt->execute($param);

		}
		}

	}

}
}


		//---↑削除投稿がPOSTされたとき
		//---↓編集番号がPOSTされたとき

if(isset($_POST["editnum"])){
if(!empty($_POST["editpass"])){ //ひとまず空だったら動かないように

	$editnum = $_POST["editnum"];
	$editpass = $_POST["editpass"];

	//---データを取得する
	$sql = 'SELECT * FROM mission5 ';
	$stmt = $pdo->query($sql);
//	$results = $stmt->fetchAll();
	foreach ($stmt as $row){

		//---idとpassが合っていたらフォームに表示する変数に代入（mission4-8改造）

		if($row['id'] == $editnum){
		if($row['pass'] == $editpass){

			$editname = $row['name'];
			$editcomment = $row['comment'];

		}
		}

	}

}
}

		//---↑編集番号がPOSTされたとき
		//---↓フォームの表示

		//---HTMLを入力するために一旦PHPを閉じる
?>

<br><br>

<!-- 投稿フォーム作成 -->

	<strong>投稿はこちら</strong><br>

	<form method="post">

	<!-- 名前の入力フォーム作成 -->
		<div>
		<label for="user_name">名前：</label><input id="user_name" type="text" name="user_name" value = <?php echo $editname; ?> >
		</div>

<br>

	<!-- コメントの入力フォーム作成 -->
		<div>
		<label for="user_comment">コメント：</label>
		<textarea id ="user_comment" name="user_comment"><?php echo $editcomment; ?></textarea>
		</div>

<br>

	<!-- パスワードの入力フォーム作成 -->
		<div>
		<label for="pass">パスワード：</label><input id="pass" type="password" name="pass" >
		</div>

<br>

	<!--送信ボタンの作成-->
		<div>
		<input type="submit" value="送信">
		</div>


	<!-- 投稿番号を表示するフォーム作成 --><!--後でhiddenに設定する-->
		<div>
		<input id="com_num" type="hidden" name="com_num" value= <?php echo $editnum; ?> >
		</div>

	<!-- 元のパスワードを表示するフォーム作成 --><!--後でhiddenに設定する-->
		<div>
		<input id="com_pass" type="hidden" name="com_pass" value= <?php echo $editpass; ?> >
		</div>

	</form>

<br><br>


<!-- 削除番号指定フォーム作成 -->

	<strong>削除番号入力フォーム</strong>

	<form method="post">

	<!-- 削除番号入力フォーム作成 -->
		<div>
		<label for="delnum">削除番号</label><input id="delnum" type="number" name="delnum">
		</div>

<br>

	<!-- 削除パスワードの入力フォーム作成 -->
		<div>
		<label for="delpass">パスワード：</label><input id="delpass" type="password" name="delpass" >
		</div>

<br>

	<!--送信ボタンの作成-->
		<div>
		<input type="submit" value="削除">
		</div>

	</form>


<br><br>


<!-- 編集番号指定フォーム作成 -->

	<strong>編集番号入力フォーム</strong>

	<form method="post">

	<!-- 編集番号入力フォーム作成 -->
		<div>
		<label for="editnum">編集番号</label><input id="editnum" type="number" name="editnum">
		</div>

<br>

	<!-- 編集パスワードの入力フォーム作成 -->
		<div>
		<label for="editpass">パスワード：</label><input id="editpass" type="password" name="editpass" >
		</div>

<br>
	<!--送信ボタンの作成-->
		<div>
		<input type="submit" value="編集">
		</div>

	</form>


<?php
		//---↑フォームの表示
		//---↓投稿の画面出力(mission4-6改造)passは表示しない

		$sql = 'SELECT * FROM mission5';
		$stmt = $pdo->query($sql);
		$results = $stmt->fetchAll();
		foreach ($results as $row){
			//$rowの中にはテーブルのカラム名が入る
			echo $row['id'].' ';
			echo $row['name'].' ';
			echo $row['comment'].' ';
			echo $row['datetime'].'<br>';
	//echo "<hr>";
	}
?>


</body>
</html>