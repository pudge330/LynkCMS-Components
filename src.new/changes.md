
[[ * Notes or to do's                                  ]]
[[ - Removed or relocated components to different repo ]]
[[ + Relocated classes                                 ]]

* Check methods on session classes to make sure non are missing and to ensue they have a similar/exact api.

* Decouple storage mechanism from Component\Storage\Storage.
  Require data adapter (DatabaseStorage|FileSystemStorage) to be constructed beforehand and passed in first argument.

* Add a `DatabaseListener` as a logger listener option. LynkCMS\Component\Logger\Listener\DatabaseListener

* Move DoctrineService\DoctrineService to Database\DoctrineService

* Rename Connection\NewConnection to Connection\ConnectionFactory

* Rename Connection\ConnectionFactory::get to Connection\ConnectionFactory::create

- Moved Service component to LynkCMS\Core repo

- Moved Package component to LynkCMS\Core repo

- Moved Mvc component to LynkCMS\Core repo

+ UUID\UUID -> Util\UUID

+ Storage\GlobalAccessContainer -> Container\GlobalArrayContainer

+ Storage\StandardContainer -> Container\StandardContainer

+ Form\Input\DefaultInput\*.php -> Form\Input\Type\*.php

----------------------------------------------------------------------------------------------------

# Core
- √ Rename Library\Form\Input\DefaultInput to Library\Form\Input\Type
- Move custom inputs from Library\Form\Input to Library\Form\Input\Types\Custom
- Move custom input processors from Library\Form\Input\Processor\Input to Library\Form\Input\Processor\Types\Custom

# Components
- Move all default input processors from LynkCMS\Core to Form\Input\Processor\Types
- Rename Form\Input\DefaultInput to Form\Input\Types
- Remove OptionTrait completely from use and replace where needed with Container\StandardContainer.
    - Move Form\OptionTrait to Container\OptionTrait -- will need LynkCMS\Core updated first before removing.

Form
  \Input
    \DefaultInput -> \Types
      AbstractInputType.php
      *Input.php
    \Processor
      \Types
        *Input.php
      AbstractInputProcessor.php
  \Processor
    AbstractFormProcessor.php
    GenericFormProcessor.php *
  \Security
    FormTokenizer.php
  \Validator
    BasicDataValidator.php
  FormError.php
  FormType.php
  FormView.php
  OptionTrait.php