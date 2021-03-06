<?php
use angelrove\membrillo\WPage\WPage;
use angelrove\membrillo\WObjects\WForm\WForm;
use angelrove\membrillo\WObjectsStatus\Event;
use App\Models\[Sample];

$controlName = 'simple';

// form_data -------
switch(Event::$EVENT) {
  case CRUD_EDIT_NEW:
    $form_data = [Sample]::findEmpty();

    // Default values
    $form_data['name'] = 'My name';
  break;

  case CRUD_EDIT_UPDATE:
    $form_data = [Sample]::findById(Event::$ROW_ID);
  break;
}

$form = new WForm($controlName, $form_data);
$form->setButtons(true, true, true);
?>

<?php WPage::get()?>
  <?php $form->get()?>

    <?=$form->getInput('name', 'Nombre', true)?>
    <?=$form->getInput('profile', 'Profile', false, 'select_array', [['1'=>'A','2'=>'B','3'=>'C']])?>

  <?php $form->get_end()?>
<?php WPage::get_end()?>
