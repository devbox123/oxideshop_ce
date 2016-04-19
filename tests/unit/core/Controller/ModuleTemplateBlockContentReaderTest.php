<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 */

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidEsales\Eshop\Core\Module\ModuleTemplateBlockContentReader;
use OxidEsales\Eshop\Core\Module\ModuleTemplateBlockPathFormatter;

class ModuleTemplateBlockContentReaderTest extends UnitTestCase
{
    public function testCanCreateClass()
    {
        oxNew(ModuleTemplateBlockContentReader::class);
    }

    public function testGetContentThrowExceptionWithoutPathFormatter()
    {
        $this->setExpectedException(oxException::class);

        $content = oxNew(ModuleTemplateBlockContentReader::class);
        $content->getContent(null);
    }

    public function testGetContentDoesNotThrowExceptionWhenValidArgumentProvided()
    {
        $pathFormatter = $this->getPathFormatterStub('pathToFile', 'some content');

        $content = oxNew(ModuleTemplateBlockContentReader::class);
        $content->getContent($pathFormatter);
    }

    public function providerGetContentReturnContentFromFileWhichWasProvided()
    {
        return [
            ['pathToFile', 'some content'],
            ['pathToFile', 'some other content'],
        ];
    }

    /**
     * @param $filePath
     * @param $content
     *
     * @dataProvider providerGetContentReturnContentFromFileWhichWasProvided
     */
    public function testGetContentReturnContentFromFileWhichWasProvided($filePath, $content)
    {
        $pathFormatter = $this->getPathFormatterStub($filePath, $content);

        $contentGetter = oxNew(ModuleTemplateBlockContentReader::class);

        $this->assertSame($content, $contentGetter->getContent($pathFormatter));
    }

    public function testGetContentThrowsExceptionWhenFileDoesNotExist()
    {
        $vfsStreamWrapper = $this->getVfsStreamWrapper();

        $filePath = $vfsStreamWrapper->getRootPath() . DIRECTORY_SEPARATOR . 'someFile';

        $exceptionMessage = "Template block file (%s) was not found for module '%s'.";
        $this->setExpectedException(oxException::class, sprintf($exceptionMessage, $filePath, 'myModuleId'));

        $pathFormatter = $this->getMock(ModuleTemplateBlockPathFormatter::class, ['getPath', 'getModuleId']);
        $pathFormatter->method('getPath')->willReturn($filePath);
        $pathFormatter->method('getModuleId')->willReturn('myModuleId');

        $content = oxNew(ModuleTemplateBlockContentReader::class);
        $content->getContent($pathFormatter);
    }

    public function testGetContentThrowsExceptionWhenFileIsNotReadable()
    {
        $notReadableMode = 000;
        $vfsStreamWrapper = $this->getVfsStreamWrapper();
        $filePath = $vfsStreamWrapper->createFile('pathToFile', 'some content');
        chmod($filePath, $notReadableMode);

        $exceptionMessage = "Template block file (%s) is not readable for module '%s'.";
        $this->setExpectedException(oxException::class, sprintf($exceptionMessage, $filePath, 'myModuleId'));

        $pathFormatter = $this->getMock(ModuleTemplateBlockPathFormatter::class, ['getPath', 'getModuleId']);
        $pathFormatter->method('getPath')->willReturn($filePath);
        $pathFormatter->method('getModuleId')->willReturn('myModuleId');

        $content = oxNew(ModuleTemplateBlockContentReader::class);
        $content->getContent($pathFormatter);
    }

    /**
     * @param $filePath
     * @param $content
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getPathFormatterStub($filePath, $content)
    {
        $vfsStreamWrapper = $this->getVfsStreamWrapper();
        $filePath = $vfsStreamWrapper->createFile($filePath, $content);

        $pathFormatter = $this->getMock(ModuleTemplateBlockPathFormatter::class, ['getPath']);
        $pathFormatter->method('getPath')->willReturn($filePath);

        return $pathFormatter;
    }
}
