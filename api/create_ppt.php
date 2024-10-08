<?php
require '../vendor/autoload.php';

use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Font;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];

    // Create new presentation
    $objPHPPowerPoint = new PhpPresentation();

    // Create slide
    $currentSlide = $objPHPPowerPoint->getActiveSlide();

    // Create a shape (text)
    $shape = $currentSlide->createRichTextShape()
                          ->setHeight(300)
                          ->setWidth(600)
                          ->setOffsetX(170)
                          ->setOffsetY(180);
    $shape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $textRun = $shape->createTextRun($title);
    $textRun->getFont()->setBold(true)
                       ->setSize(60)
                       ->setColor(new Color('FFE06B20'));

    $shape->createBreak();
    $textRun = $shape->createTextRun($content);
    $textRun->getFont()->setSize(30)
                       ->setColor(new Color('FF000000'));

    // Save file
    $oWriterPPTX = IOFactory::createWriter($objPHPPowerPoint, 'PowerPoint2007');
    $oWriterPPTX->save(__DIR__ . "../files/presentation.pptx");
    header("Location: /files/presentation.pptx");

    echo "PowerPoint presentation created successfully!";
}
?>
