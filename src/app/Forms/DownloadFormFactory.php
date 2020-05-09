<?php

namespace App\Forms;

use App\Model\ExcelManager;
use Nette;
use Nette\Application\UI\Form;


/**
 * Class DownloadFormFactory
 * Factory creating form that exports data into excel on success.
 * @package App\Forms
 * @author Petr Zeman
 * @version Summer 2020
 */
final class DownloadFormFactory
{
    use Nette\SmartObject;

    /** @var FormFactory */
    private $factory;

    /** @var ExcelManager @inject */
    private $excelManager;


    /**
     * DownloadFormFactory constructor.
     * @param FormFactory $factory
     * @param ExcelManager $excelManager
     */
    public function __construct(FormFactory $factory, ExcelManager $excelManager)
    {
        $this->factory = $factory;
        $this->excelManager = $excelManager;
    }

    /** Function that creates form for exporting data to excel
     * @param callable $onSuccess
     * @return Form - Form for exporting data to excel.
     */
    public function create(callable $onSuccess): Form
    {
        $form = $this->factory->create();
        $form->addSubmit('accept', 'Download');
        $form->onSuccess[] = [$this, 'downloadFormSucceeded'];

        return $form;

    }

    /** Function that exports data to excel on success.
     * @param $form - Form that reports request for exporting data.
     * @param $values - Values of successful form.
     */
    public function downloadFormSucceeded($form, $values)
    {
        $this->excelManager->exportExcel();
    }

}