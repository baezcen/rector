<?php

namespace Rector\Nette\Tests\Rector\MethodCall\AddDatePickerToDateControlRector\Fixture;

use Nette\Application\UI\Form;

class SomeClass
{
    public function run()
    {
        $form = new Form();
        $form->addDatePicker('key', 'Label');
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\MethodCall\AddDatePickerToDateControlRector\Fixture;

use Nette\Application\UI\Form;

class SomeClass
{
    public function run()
    {
        $form = new Form();
        $keyDateControl = $form['key'] = new \Nextras\FormComponents\Controls\DateControl('Label');
    }
}

?>
