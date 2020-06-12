<?php

namespace App\Forms;

use App\Model\ExcelManager;
use App\Model\FileFaultException;
use Nette;
use Nette\Application\UI\Form;

/**
 * Class UploadFormFactory
 * Factory creating form that imports data from excel.
 * @package App\Forms
 * @author Petr Zeman
 * @version Summer 2020
 */
final class UploadFormFactory
{
    use Nette\SmartObject;

    /** @var FormFactory */
    private $factory;

    /** @var ExcelManager @inject */
    private $excelManager;


    /**
     * UploadFormFactory constructor.
     * @param FormFactory $factory
     * @param ExcelManager $excelManager
     */
    public function __construct(FormFactory $factory, ExcelManager $excelManager)
    {
        $this->factory = $factory;
        $this->excelManager = $excelManager;
    }


    /** Function that creates form for uploading data from excel
     * @param callable $onSuccess
     * @return Form - Form for uploading data from excel
     */
    public function create(callable $onSuccess): Form
    {
        $form = $this->factory->create();
        $form->addUpload('excel');
        $form->addSubmit('send', 'upload');
        $form->onSuccess[] = [$this, 'uploadFormSucceeded'];

        return $form;

    }

    /** Function that imports data from inserted excel on success.
     * @param $form - Form from which data is imported.
     * @param $values - Values of successful form.
     */
    public function uploadFormSucceeded($form, $values)
    {
        if ($values->excel->hasFile()) {
            $file = $values->excel;
            try {
                $this->excelManager->importExcel($file);
            }catch(FileFaultException $e){
                $form->addError("Problem with inserted file!\r\n Sheet: ". $e->getSheet().",\r\n Line: ".$e->getSheetLine().".");
            }catch(\Exception $e){
                $form->addError("There was a problem during file insertion, please try again.");
            }

        } else {
            $form->addError("No file inserted!");
        }
    }

}