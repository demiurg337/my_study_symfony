<?php

namespace Symfony\Component\HttpFoundation\Tests\File;
var_dump(require_once __DIR__.'/../../File/UploadedFile.php');
//use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
class UploadedFileTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        /*
        Тест прорпускается если
        не включена подгрузка файлов
        */
        if (!ini_get('file_uploads')) {
            $this->markTestSkipped('Uplad files are disables');
        }
    }


    public function testConstructWhenNotExistFile()
    {
        $this->setExpectedException('Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException');

        new UploadedFile(__DIR__.'/not_exist','not_exist.jpg', null);
    }

    public function testFileUploadWithNoMimeType()
    {

        $pathTestImg =  __DIR__.'/Fixtures/test.gif'; 
        $img = new UploadedFile(
            $pathTestImg,
            'original_test.gif',
            null,
            filesize($pathTestImg),
            UPLOAD_ERR_OK
            );

       $this->assertEquals('application/octet-stream', $img->getClientMimeType());

    }

    public function testGuessExtension()
    {

        $pathTestImg =  __DIR__.'/Fixtures/test.gif'; 
        $img = new UploadedFile(
            $pathTestImg,
            'original_test.gif',
            'image/gif',
            filesize($pathTestImg),
            UPLOAD_ERR_OK
        );

        $this->assertEquals('gif', $img->guessClientExtension());
    }

    public function testGuessExtensionWithWrongMimeType()
    {

        $pathTestImg =  __DIR__.'/Fixtures/test.gif'; 
        $img = new UploadedFile(
            $pathTestImg,
            'original_test.gif',
            'image/png',
            filesize($pathTestImg),
            UPLOAD_ERR_OK
        );

        $this->assertEquals('png', $img->guessClientExtension());
    }
    public function testErrorIsOkByDefault()
    {
        $file = new UploadedFile(
            __DIR__.'/Fixtures/test.gif',
            'original.gif',
            'image/gif',
            filesize(__DIR__.'/Fixtures/test.gif'),
            null
        );

        $this->assertEquals(UPLOAD_ERR_OK, $file->getError());
    }

    public function testGetClientOriginalName()
    {
        $file = new UploadedFile(
            __DIR__.'/Fixtures/test.gif',
            'original.gif',
            'image/gif',
            filesize(__DIR__.'/Fixtures/test.gif'),
            null
        );

        $this->assertEquals('original.gif', $file->getClientOriginalName());
    }

    public function testGetClientOriginalExtension()
    {
        $file = new UploadedFile(
            __DIR__.'/Fixtures/test.gif',
            'original.gif',
            'image/gif',
            filesize(__DIR__.'/Fixtures/test.gif'),
            null
        );

        $this->assertEquals('gif', $file->getClientOriginalExtension());
    }

    /*
    * @expectedException Symfony\Component\HttpFoundation\File\Exception\FileException
    */
    public function testMoveFileToDirNotAllowed()
    {
        $file = new UploadedFile(
            __DIR__.'/Fixtures/test.gif',
            'original.gif',
            'image/gif',
            filesize(__DIR__.'/Fixtures/test.gif'),
            null
        );
        
        $file->move(__DIR__.'/Fixtures/directory/'); 
    }
    public function testMoveLocalFileIsAllowedInTestMode()
    {
        $path = __DIR__.'/Fixtures/test.copy.gif';
        $targetDir = __DIR__.'/Fixtures/directory';
        $targetPath = $targetDir.'/test.copy.gif';
        @unlink($path);
        @unlink($targetPath);
        copy(__DIR__.'/Fixtures/test.gif', $path);

        $file = new UploadedFile(
            $path,
            'original.gif',
            'image/gif',
            filesize($path),
            UPLOAD_ERR_OK,
            true
        );

        $movedFile = $file->move(__DIR__.'/Fixtures/directory');

        $this->assertTrue(file_exists($targetPath));
        $this->assertFalse(file_exists($path));
        $this->assertEquals(realpath($targetPath), $movedFile->getRealPath());

        @unlink($targetPath);
    }

    public function testGetClientOriginalNameSanitizeFilename()
    {
        $file = new UploadedFile(
            __DIR__.'/Fixtures/test.gif',
            '../../original.gif',
            'image/gif',
            filesize(__DIR__.'/Fixtures/test.gif'),
            null
        );

        $this->assertEquals('original.gif', $file->getClientOriginalName());
    }

    public function testGetSize()
    {
        $file = new UploadedFile(
            __DIR__.'/Fixtures/test.gif',
            'original.gif',
            'image/gif',
            filesize(__DIR__.'/Fixtures/test.gif'),
            null
        );

        $this->assertEquals(filesize(__DIR__.'/Fixtures/test.gif'), $file->getSize());

        $file = new UploadedFile(
            __DIR__.'/Fixtures/test',
            'original.gif',
            'image/gif'
        );

        $this->assertEquals(filesize(__DIR__.'/Fixtures/test'), $file->getSize());
    }

    public function testGetExtension()
    {
        $file = new UploadedFile(
            __DIR__.'/Fixtures/test.gif',
            'original.gif',
            null
        );

        $this->assertEquals('gif', $file->getExtension());
    }

    public function testIsValid()
    {
        $file = new UploadedFile(
            __DIR__.'/Fixtures/test.gif',
            'original.gif',
            null,
            filesize(__DIR__.'/Fixtures/test.gif'),
            UPLOAD_ERR_OK,
                true
        );

        $this->assertTrue($file->isValid());
    }

    
    /**
    * @dataProvider uploadedFileErrorProvider
    */
    public function testIsInvalidOnUpladError($error)
    {
        
        $file = new UploadedFile(
            __DIR__.'/Fixtures/test.gif',
            'original.gif',
            null,
            filesize(__DIR__.'/Fixtures/test.gif'),
            $error
        );

        $this->assertFalse($file->isValid());
    }

    public function uploadedFileErrorProvider()
    {
        return array(
            array(UPLOAD_ERR_INI_SIZE),
            array(UPLOAD_ERR_FORM_SIZE),
            array(UPLOAD_ERR_PARTIAL),
            array(UPLOAD_ERR_NO_TMP_DIR),
            array(UPLOAD_ERR_EXTENSION),
        );
    }
    
    public function testIsInvalidIfNotHttpUpload()
    {
        $file = new UploadedFile(
            __DIR__.'/Fixtures/test.gif',
            'original.gif',
            null,
            filesize(__DIR__.'/Fixtures/test.gif'),
            UPLOAD_ERR_OK
        );

        $this->assertFalse($file->isValid());
    }

}
