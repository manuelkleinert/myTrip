<?php 

/** 
* Generated at: 2019-08-08T16:39:25+02:00
* Inheritance: no
* Variants: no
* Changed by: admin (2)
* IP: 192.168.144.1


Fields Summary: 
- title [input]
- subTitle [wysiwyg]
- text [wysiwyg]
- from [date]
- to [date]
- owner [manyToOneRelation]
- share [manyToManyObjectRelation]
- private [checkbox]
*/ 


return Pimcore\Model\DataObject\ClassDefinition::__set_state(array(
   'id' => '2',
   'name' => 'Journey',
   'description' => '',
   'creationDate' => 0,
   'modificationDate' => 1565275165,
   'userOwner' => 2,
   'userModification' => 2,
   'parentClass' => '',
   'listingParentClass' => '',
   'useTraits' => '',
   'listingUseTraits' => '',
   'encryption' => false,
   'encryptedTables' => 
  array (
  ),
   'allowInherit' => false,
   'allowVariants' => NULL,
   'showVariants' => false,
   'layoutDefinitions' => 
  Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
     'fieldtype' => 'panel',
     'labelWidth' => 100,
     'layout' => NULL,
     'border' => false,
     'icon' => NULL,
     'name' => 'pimcore_root',
     'type' => NULL,
     'region' => NULL,
     'title' => NULL,
     'width' => NULL,
     'height' => NULL,
     'collapsible' => false,
     'collapsed' => false,
     'bodyStyle' => NULL,
     'datatype' => 'layout',
     'permissions' => NULL,
     'childs' => 
    array (
      0 => 
      Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
         'fieldtype' => 'panel',
         'labelWidth' => 100,
         'layout' => NULL,
         'border' => false,
         'icon' => '',
         'name' => 'Layout',
         'type' => NULL,
         'region' => NULL,
         'title' => '',
         'width' => NULL,
         'height' => NULL,
         'collapsible' => false,
         'collapsed' => false,
         'bodyStyle' => '',
         'datatype' => 'layout',
         'permissions' => NULL,
         'childs' => 
        array (
          0 => 
          Pimcore\Model\DataObject\ClassDefinition\Data\Input::__set_state(array(
             'fieldtype' => 'input',
             'width' => NULL,
             'queryColumnType' => 'varchar',
             'columnType' => 'varchar',
             'columnLength' => 190,
             'phpdocType' => 'string',
             'regex' => '',
             'unique' => false,
             'showCharCount' => false,
             'name' => 'title',
             'title' => 'Title',
             'tooltip' => '',
             'mandatory' => true,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => '',
             'permissions' => NULL,
             'datatype' => 'data',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => false,
             'visibleSearch' => false,
          )),
          1 => 
          Pimcore\Model\DataObject\ClassDefinition\Data\Wysiwyg::__set_state(array(
             'fieldtype' => 'wysiwyg',
             'width' => '',
             'height' => '',
             'queryColumnType' => 'longtext',
             'columnType' => 'longtext',
             'phpdocType' => 'string',
             'toolbarConfig' => '',
             'excludeFromSearchIndex' => false,
             'name' => 'subTitle',
             'title' => 'Sub Title',
             'tooltip' => '',
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => '',
             'permissions' => NULL,
             'datatype' => 'data',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => false,
             'visibleSearch' => false,
          )),
          2 => 
          Pimcore\Model\DataObject\ClassDefinition\Data\Wysiwyg::__set_state(array(
             'fieldtype' => 'wysiwyg',
             'width' => '',
             'height' => '',
             'queryColumnType' => 'longtext',
             'columnType' => 'longtext',
             'phpdocType' => 'string',
             'toolbarConfig' => '',
             'excludeFromSearchIndex' => false,
             'name' => 'text',
             'title' => 'Text',
             'tooltip' => '',
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => '',
             'permissions' => NULL,
             'datatype' => 'data',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => false,
             'visibleSearch' => false,
          )),
          3 => 
          Pimcore\Model\DataObject\ClassDefinition\Data\Date::__set_state(array(
             'fieldtype' => 'date',
             'queryColumnType' => 'bigint(20)',
             'columnType' => 'bigint(20)',
             'phpdocType' => '\\Carbon\\Carbon',
             'defaultValue' => NULL,
             'useCurrentDate' => false,
             'name' => 'from',
             'title' => 'From',
             'tooltip' => '',
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => '',
             'permissions' => NULL,
             'datatype' => 'data',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => false,
             'visibleSearch' => false,
          )),
          4 => 
          Pimcore\Model\DataObject\ClassDefinition\Data\Date::__set_state(array(
             'fieldtype' => 'date',
             'queryColumnType' => 'bigint(20)',
             'columnType' => 'bigint(20)',
             'phpdocType' => '\\Carbon\\Carbon',
             'defaultValue' => NULL,
             'useCurrentDate' => false,
             'name' => 'to',
             'title' => 'To',
             'tooltip' => '',
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => '',
             'permissions' => NULL,
             'datatype' => 'data',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => false,
             'visibleSearch' => false,
          )),
          5 => 
          Pimcore\Model\DataObject\ClassDefinition\Data\ManyToOneRelation::__set_state(array(
             'fieldtype' => 'manyToOneRelation',
             'width' => '',
             'assetUploadPath' => '',
             'relationType' => true,
             'queryColumnType' => 
            array (
              'id' => 'int(11)',
              'type' => 'enum(\'document\',\'asset\',\'object\')',
            ),
             'phpdocType' => '\\Pimcore\\Model\\Document\\Page | \\Pimcore\\Model\\Document\\Snippet | \\Pimcore\\Model\\Document | \\Pimcore\\Model\\Asset | \\Pimcore\\Model\\DataObject\\AbstractObject',
             'objectsAllowed' => true,
             'assetsAllowed' => false,
             'assetTypes' => 
            array (
            ),
             'documentsAllowed' => false,
             'documentTypes' => 
            array (
            ),
             'lazyLoading' => true,
             'classes' => 
            array (
              0 => 
              array (
                'classes' => 'MembersUser',
              ),
            ),
             'pathFormatterClass' => '',
             'name' => 'owner',
             'title' => 'Owner',
             'tooltip' => '',
             'mandatory' => true,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => '',
             'permissions' => NULL,
             'datatype' => 'data',
             'invisible' => false,
             'visibleGridView' => false,
             'visibleSearch' => false,
          )),
          6 => 
          Pimcore\Model\DataObject\ClassDefinition\Data\ManyToManyObjectRelation::__set_state(array(
             'fieldtype' => 'manyToManyObjectRelation',
             'width' => '',
             'height' => '',
             'maxItems' => '',
             'queryColumnType' => 'text',
             'phpdocType' => 'array',
             'relationType' => true,
             'visibleFields' => 
            array (
            ),
             'optimizedAdminLoading' => false,
             'visibleFieldDefinitions' => 
            array (
            ),
             'lazyLoading' => true,
             'classes' => 
            array (
              0 => 
              array (
                'classes' => 'MembersUser',
              ),
            ),
             'pathFormatterClass' => '',
             'name' => 'share',
             'title' => 'share',
             'tooltip' => '',
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => '',
             'permissions' => NULL,
             'datatype' => 'data',
             'invisible' => false,
             'visibleGridView' => false,
             'visibleSearch' => false,
          )),
          7 => 
          Pimcore\Model\DataObject\ClassDefinition\Data\Checkbox::__set_state(array(
             'fieldtype' => 'checkbox',
             'defaultValue' => NULL,
             'queryColumnType' => 'tinyint(1)',
             'columnType' => 'tinyint(1)',
             'phpdocType' => 'boolean',
             'name' => 'private',
             'title' => 'Private Journey',
             'tooltip' => '',
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => NULL,
             'style' => '',
             'permissions' => NULL,
             'datatype' => 'data',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => false,
             'visibleSearch' => false,
          )),
        ),
         'locked' => false,
      )),
    ),
     'locked' => false,
  )),
   'icon' => '',
   'previewUrl' => '',
   'group' => '',
   'showAppLoggerTab' => false,
   'linkGeneratorReference' => '',
   'propertyVisibility' => 
  array (
    'grid' => 
    array (
      'id' => true,
      'key' => false,
      'path' => true,
      'published' => true,
      'modificationDate' => true,
      'creationDate' => true,
    ),
    'search' => 
    array (
      'id' => true,
      'key' => false,
      'path' => true,
      'published' => true,
      'modificationDate' => true,
      'creationDate' => true,
    ),
  ),
   'dao' => NULL,
));
