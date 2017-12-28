<?php
/**
 
 * 腾讯COS封装类
 
 * @date: 2017年12月28日 上午10:43:13
 
 * @author: chenxp
 
 */
require (__DIR__ . DIRECTORY_SEPARATOR . 'cos-autoloader.php');

class cos4tencent
{

    var $cosClient;

    private $appId = '1255602627';

    private $secreId = 'AKIDsrgNwNBr9JH4f56wUFwTqMwRlw1As3qS';

    private $secreKey = 'kiKsJpFzj2HVeXz5o6Ok2FSZWNFF8ISU';

    private $region = 'sh';

    function __construct($appid, $secreId, $secreKey, $region = 'sh')
    {
        $this->appId = $appid;
        $this->secreId = $secreId;
        $this->secreKey = $secreKey;
        $this->region = $region;
        
        $this->cosClient = new Qcloud\Cos\Client(array(
            'region' => $region,
            'credentials' => array(
                'appId' => $appId,
                'secretId' => $secreKey,
                'secretKey' => $secreKey
            )
        ));

        /**
         *
         * 返回所有Buckets
         *
         * @date: 2017年12月28日 上午10:52:11
         *
         * @author : chenxp
         *        
         * @param : $GLOBALS            
         *
         * @return :Buckets数组
         *
         */
        function listBuckets()
        {
            try {
                $result = $this->cosClient->listBuckets();
                return ($result);
            } catch (\Exception $e) {
                echo "$e\n";
            }
        }

        /**
         *
         * 创建指定名字的bucket
         *
         * @date: 2017年12月28日 上午10:53:14
         *
         * @author : chenxp
         *        
         * @param : $bucket，指定bucket名            
         *
         * @return :
         *
         */
        function createBucket($bucket)
        {
            try {
                $result = $this->cosClient->createBucket(array(
                    'Bucket' => $bucket
                ));
                return ($result);
            } catch (\Exception $e) {
                echo "$e\n";
            }
        }

        /**
         *
         * 上传指定文件到指定Bucket
         *
         * @date: 2017年12月28日 上午10:57:51
         *
         * @author : chenxp
         *        
         * @param : $bucket
         *            指定bucket
         * @param
         *            :$key 可带路径的文件名
         *            
         * @return :
         *
         */
        function uploadbigfile($bucket, $key)
        {
            try {
                $result = $this->cosClient->upload($bucket = 'testbucket', $key = '111.txt', $body = str_repeat('a', 5 * 1024 * 1024));
                return ($result);
            } catch (\Exception $e) {
                echo "$e\n";
            }
        }

        /**
         *
         * 上传本地某目录下的文件到指定bucket
         *
         * @date: 2017年12月28日 上午11:02:38
         *
         * @author : chenxp
         *        
         * @param : $bucket
         *            指定bucket
         * @param : $key
         *            保存到bucket中的文件名
         * @param
         *            ：@file 本地带路径的文件
         *            
         * @return :
         *
         */
        function putObject($bucket, $key, $file)
        {
            try {
                $result = $this->cosClient->putObject(array(
                    'Bucket' => $bucket,
                    'Key' => $key,
                    'Body' => $file // fopen('***', 'rb')
                ));
                return ($result);
            } catch (\Exception $e) {
                echo "$e\n";
            }
        }

        function getObject($bucket, $key, $saveAs)
        {
            try {
                $result = $cosClient->getObject(array(
                    'Bucket' => $bucket,
                    'Key' => $key,
                    'SaveAs' => $saveAs
                ));
                return ($result['Body']);
            } catch (\Exception $e) {
                echo "$e\n";
            }
        }
    }
    // #deleteObject
    // try {
    // $result = $cosClient->deleteObject(array(
    // 'Bucket' => 'lewzylu02',
    // 'Key' => '111.txt'));
    // print_r($result);
    // } catch (\Exception $e) {
    // echo "$e\n";
    // }
    // #deleteObjects
    // try {
    // $result = $cosClient->deleteObjects(array(
    // // Bucket is required
    // 'Bucket' => 'string',
    // // Objects is required
    // 'Objects' => array(
    // array(
    // // Key is required
    // 'Key' => 'string',
    // 'VersionId' => 'string',
    // ),
    // // ... repeated
    // ),
    // ));
    // print_r($result);
    // } catch (\Exception $e) {
    // echo "$e\n";
    // }
    //
    // #deleteBucket
    // try {
    // $result = $cosClient->deleteBucket(array(
    // 'Bucket' => 'testbucket'));
    // print_r($result);
    // } catch (\Exception $e) {
    // echo "$e\n";
    // }
    //
    // #headObject
    // try {
    // $result = $cosClient->headObject(array(
    // 'Bucket' => 'testbucket',
    // 'Key' => '11'));
    // print_r($result);
    // } catch (\Exception $e) {
    // echo "$e\n";
    // }
    //
    // #listObjects
    // try {
    // $result = $cosClient->headObject(array(
    // 'Bucket' => 'testbucket',
    // 'Key' => '11'));
    // print_r($result);
    // } catch (\Exception $e) {
    // echo "$e\n";
    // }
    //
    // listObjects
    /*
     * try {
     * $result = $cosClient->listObjects(array(
     * 'Bucket' => 'bucket1205'));
     * print_r($result);
     * } catch (\Exception $e) {
     * echo "$e\n";
     * }
     */
    // #putObjectUrl
    // try {
    // $bucket = 'testbucket';
    // $key = 'hello.txt';
    // $region = 'cn-south';
    // $url = "/{$key}";
    // $request = $cosClient->get($url);
    // $signedUrl = $cosClient->getObjectUrl($bucket, $key, '+10 minutes');
    // echo ($signedUrl);
    //
    // } catch (\Exception $e) {
    // echo "$e\n";
    // }
    // #putBucketACL
    // try {
    // $result = $cosClient->PutBucketAcl(array(
    // 'Bucket' => 'testbucket',
    // 'Grants' => array(
    // array(
    // 'Grantee' => array(
    // 'DisplayName' => 'qcs::cam::uin/327874225:uin/327874225',
    // 'ID' => 'qcs::cam::uin/327874225:uin/327874225',
    // 'Type' => 'CanonicalUser',
    // ),
    // 'Permission' => 'FULL_CONTROL',
    // ),
    // // ... repeated
    // ),
    // 'Owner' => array(
    // 'DisplayName' => 'qcs::cam::uin/3210232098:uin/3210232098',
    // 'ID' => 'qcs::cam::uin/3210232098:uin/3210232098',
    // ),));
    // print_r($result);
    // } catch (\Exception $e) {
    // echo "$e\n";
    // }
    // #getBucketACL
    // try {
    // $result = $cosClient->GetBucketAcl(array(
    // 'Bucket' => 'testbucket',));
    // print_r($result);
    // } catch (\Exception $e) {
    // echo "$e\n";
    // }
    //
    // #putObjectACL
    // try {
    // $result = $cosClient->PutBucketAcl(array(
    // 'Bucket' => 'testbucket',
    // 'Grants' => array(
    // array(
    // 'Grantee' => array(
    // 'DisplayName' => 'qcs::cam::uin/327874225:uin/327874225',
    // 'ID' => 'qcs::cam::uin/327874225:uin/327874225',
    // 'Type' => 'CanonicalUser',
    // ),
    // 'Permission' => 'FULL_CONTROL',
    // ),
    // // ... repeated
    // ),
    // 'Owner' => array(
    // 'DisplayName' => 'qcs::cam::uin/3210232098:uin/3210232098',
    // 'ID' => 'qcs::cam::uin/3210232098:uin/3210232098',
    // ),));
    // print_r($result);
    // } catch (\Exception $e) {
    // echo "$e\n";
    // }
    //
    // #getObjectACL
    // try {
    // $result = $cosClient->getObjectAcl(array(
    // 'Bucket' => 'testbucket',
    // 'Key' => '11'));
    // print_r($result);
    // } catch (\Exception $e) {
    // echo "$e\n";
    // }
    // #putBucketLifecycle
    // try {
    // $result = $cosClient->putBucketLifecycle(array(
    // // Bucket is required
    // 'Bucket' => 'lewzylu02',
    // // Rules is required
    // 'Rules' => array(
    // array(
    // 'Expiration' => array(
    // 'Days' => 1,
    // ),
    // 'ID' => 'id1',
    // 'Filter' => array(
    // 'Prefix' => 'documents/'
    // ),
    // // Status is required
    // 'Status' => 'Enabled',
    // 'Transition' => array(
    // 'Days' => 100,
    // 'StorageClass' => 'NEARLINE',
    // ),
    // // ... repeated
    // ),
    // )));
    // print_r($result);
    // } catch (\Exception $e) {
    // echo "$e\n";
    // }
    // #getBucketLifecycle
    // try {
    // $result = $cosClient->getBucketLifecycle(array(
    // // Bucket is required
    // 'Bucket' =>'lewzylu02',
    // ));
    // print_r($result);
    // } catch (\Exception $e) {
    // echo "$e\n";
    // }
    //
    // #deleteBucketLifecycle
    // try {
    // $result = $cosClient->deleteBucketLifecycle(array(
    // // Bucket is required
    // 'Bucket' =>'lewzylu02',
    // ));
    // print_r($result);
    // } catch (\Exception $e) {
    // echo "$e\n";
    // }
    // putBucketCors
    /*
     * try {
     * $result = $cosClient->putBucketCors(array(
     * // Bucket is required
     * 'Bucket' => 'lewzylu02',
     * // CORSRules is required
     * 'CORSRules' => array(
     * array(
     * 'ID' => '1234',
     * 'AllowedHeaders' => array('*'),
     * // AllowedMethods is required
     * 'AllowedMethods' => array('PUT'),
     * // AllowedOrigins is required
     * 'AllowedOrigins' => array('http://www.qq.com', ),
     * // 'ExposeHeaders' => array('*', ),
     * // 'MaxAgeSeconds' => 1,
     * ),
     * // ... repeated
     * ),
     * ));
     * print_r($result);
     * } catch (\Exception $e) {
     * echo "$e\n";
     * }
     * #getBucketCors
     * try {
     * $result = $cosClient->getBucketCors(array(
     * // Bucket is required
     * 'Bucket' => 'lewzylu02',
     * ));
     * print_r($result);
     * } catch (\Exception $e) {
     * echo "$e\n";
     * }
     * #deleteBucketCors
     * try {
     * $result = $cosClient->deleteBucketCors(array(
     * // Bucket is required
     * 'Bucket' => 'lewzylu02',
     * ));
     * print_r($result);
     * } catch (\Exception $e) {
     * echo "$e\n";
     * }
     * //#copyobject
     * //try {
     * // $result = $cosClient->copyObject(array(
     * // // Bucket is required
     * // 'Bucket' => 'lewzylu02',
     * // // CopySource is required
     * // 'CopySource' => 'lewzylu03-1252448703.cos.ap-guangzhou.myqcloud.com/tox.ini',
     * // // Key is required
     * // 'Key' => 'string',
     * // ));
     * // print_r($result);
     * //} catch (\Exception $e) {
     * // echo "$e\n";
     * //}
     * //Copy
     * //try {
     * // $result = $cosClient->Copy($bucket = 'lewzylu02',
     * // $key = 'cmake-3.8.2为.tar.gz',
     * // $copysource = 'lewzylu-1252448703.cos.ap-guangzhou.myqcloud.com/cmake-3.8.2为.tar.gz');
     * // print_r($result);
     * //} catch (\Exception $e) {
     * // echo "$e\n";
     * //}
     */
}