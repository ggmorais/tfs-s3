<?php 

require_once 'config.php';

$TFSE

class TFS {

	public function getList()//create a .txt file with all branches inside tfvc
	{

		$file = 'branch.txt';

		$shell = 'git tfs list-remote-branches ' . $TFSE['TFS_HOST'] . ' > ' . $file;

		shell_exec($shell);

		if (file_exists($file)) {
			if (filesize($file) > 0) return true;
		}

		return false;

	}

	public function readList()
	{

		$file = file('branch.txt');

		$branches = [];

		foreach ($file as $line) {

			$remove = ['+', '[', ']', '*', '|'];

			if (strstr($line, '$')) {

				$line = str_replace($remove, '', $line);

				$line = trim($line);

				if ($line[0] == '-') {
					$line = substr($line, 1);
				}
 
				$branches[] = trim($line);
			}

		}

		if (count($branches) > 0) return $branches;

		return false;

	}

	public function getZip($target)//download a zip file using the option that tfvc offers
	{	

		$parts = explode('/', $target);

		if (count($parts) > 3) {
			$filename = $parts[1] . '.' . $parts[2] . '.' . end($parts) . '.zip';
		} else {
			$filename = $parts[1] . '.' . end($parts) . '.zip';
		}

		$to_url = urlencode($target);

		$url = TFS_HOST . '/' . $parts[1]. '/_api/_versioncontrol/itemContentZipped?path=' .$to_url. '&__v=3';

		$ch = curl_init($url);

		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_USERPWD, $TFSE['TFS_USERNAME'] . ':' . $TFSE['TFS_PASSWORD']);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/zip',
			'Transfer-Encoding: chunked'
		]);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		

		$data = curl_exec($ch);

		curl_close($ch);

		file_put_contents('temp/'.$filename, $data);

		if (file_exists('temp/'.$filename)) return $filename;

		return false;

	}

}


$aws = new AWS;

$files = array_slice(TFS::readList(), 0, 20);

foreach ($files as $filex) {

	$file = TFS::getZip($filex);

	if (file_exists('temp/'.$file)) {
		$aws->upload($file);
	}
}



?>