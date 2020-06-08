<?php 

require_once 'config.php';
require 'aws/vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

$S3 = $env["S3"];

class AWS {

	public function __construct()
	{
		try {

			$this->s3 = new S3Client([
				'version' => 'latest',
				'region' => $S3['S3_REGION'],
				'credentials' => [
					'key' => $S3['S3_KEY'],
					'secret' => $S3['S3_SECRET']
				]
			]);
		} catch (Exception $e) {

			echo 'ERROR '.$e;

		}
	}
 

	public function list()
	{

		$s3 = $this->s3;

		try {

			$list = $s3->getPaginator('ListObjects', [
				'Bucket' => $S3['S3_BUCKET']
			]);

			foreach ($list as $obj) {
				$objs[] = $obj['Contents'];
			}

			return $objs;

		} catch (Exception $e) {

			return 'ERROR '.$e;

		}
	}


	public function upload($obj)
	{

		$s3 = $this->s3;

		try {

			if (file_exists('temp/'.$obj)) {

				$upload = $s3->putObject([
					'Bucket' => $S3['S3_BUCKET'],
					'Key' => $obj,
					'SourceFile' => 'temp/'.$obj
				]);

				return true;
			}
		} catch (Exception $e) {
			return 'ERROR '.$e;
		}

	}

	public function objExists($obj)
	{
		$s3 = $this->s3;

		$info = $s3->doesObjectExist($S3['S3_BUCKET'], $obj);

		return $info;

	}

}




?>