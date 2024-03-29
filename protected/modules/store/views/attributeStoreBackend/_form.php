<?php
/**
 * @var $this AttributeStoreBackendController
 * @var $model Attribute
 * @var $form \yupe\widgets\ActiveForm
 */
?>

<?php
/**
 * @var $model Attribute
 */

$form = $this->beginWidget(
    '\yupe\widgets\ActiveForm',
    [
        'id' => 'attribute-form',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
        'htmlOptions' => ['class' => 'well'],
    ]
); ?>
<div class="alert alert-info">
    <?= Yii::t('StoreModule.store', 'Fields with'); ?>
    <span class="required">*</span>
    <?= Yii::t('StoreModule.store', 'are required'); ?>
</div>

<?= $form->errorSummary($model); ?>

<div class='row'>
    <div class="col-sm-4">
        <?= $form->dropDownListGroup(
            $model,
            'type',
            [
                'widgetOptions' => [
                    'data' => $model->getTypesList(),
                    'htmlOptions' => [
                        'empty' => '---',
                        'id' => 'attribute-type',
                    ],
                ],
            ]
        ); ?>
    </div>

    <div class="col-sm-4">
        <?= $form->dropDownListGroup(
            $model,
            'group_id',
            [
                'widgetOptions' => [
                    'data' => AttributeGroup::model()->getFormattedList(),
                    'htmlOptions' => [
                        'empty' => '---',
                    ],
                ],
            ]
        ); ?>
    </div>
</div>


<div class='row'>
    <div class="col-sm-4">
        <?= $form->textFieldGroup($model, 'title'); ?>
    </div>
    <div class="col-sm-4">
        <?= $form->slugFieldGroup($model, 'name', ['sourceAttribute' => 'title']); ?>
    </div>
</div>

<div class='row'>
    <div class="col-sm-8">
        <?= $form->textAreaGroup($model, 'description', ['rows' => 30, 'class' => 'form-control']); ?>
    </div>
</div>

<div class='row'>
    <div class="col-sm-4">
        <?= $form->textFieldGroup($model, 'unit'); ?>
    </div>
</div>

<div class='row'>
    <div class="col-sm-8">
        <?= $form->checkBoxGroup($model, 'required'); ?>
    </div>
</div>

<div class='row'>
    <div class="col-sm-8">
        <?= $form->checkBoxGroup($model, 'is_filter'); ?>
    </div>
</div>

<?php if ($model->getIsNewRecord()): ?>
    <div class="row">
        <div id="options"
             class="<?= !$model->isMultipleValues() ? 'hidden' : ''; ?> col-sm-5">
            <div class="row form-group">
                <div class="col-sm-12">
                    <?= Yii::t("StoreModule.store", "Each option value must be on a new line."); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <?= CHtml::activeTextArea($model, 'rawOptions',
                        ['rows' => 10, 'class' => 'form-control']); ?>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div id="options" class="<?= !$model->isMultipleValues() ? 'hidden' : ''; ?>">
        <div class="row">
            <div class="col-md-2">
                <?= CHtml::label('Фильтр', null, ['class' => 'control-label']); ?>
                <?= CHtml::textField('AttributeOption[name]', null, ['class' => 'form-control']); ?>
            </div>
            <div class="col-md-2 <?= $model->isTypeList() ? 'hidden' : ''; ?>">
                <?= CHtml::label('Цвет', null, ['class' => 'control-label']); ?>
                <?= CHtml::textField('AttributeOption[color]', null, ['class' => 'form-control']); ?>
            </div>
            <div class="col-md-2">
                <?= CHtml::button(Yii::t('StoreModule.store', 'Add'), [
                    'class' => 'btn btn-success',
                    'id' => 'add-option-btn',
                    'data-url' => Yii::app()->createUrl('/store/attributeStoreBackend/addOption', ['id' => $model->id]),
                ]); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?php $this->widget(
                    'yupe\widgets\CustomGridView',
                    [
                        'hideBulkActions' => true,
                        'id' => 'attributes-options-grid',
                        'sortableRows' => true,
                        'sortableAjaxSave' => true,
                        'sortableAttribute' => 'position',
                        'sortableAction' => '/store/attributeStoreBackend/sortoptions',
                        'type' => 'condensed',
                        'ajaxUrl' => Yii::app()->createUrl('/store/attributeStoreBackend/update', ['id' => $model->id]),
                        'template' => "{items}\n{pager}<br/><br/>",
                        'dataProvider' => new CActiveDataProvider('AttributeOption',
                            [
                                'criteria' => [
                                    'condition' => 'attribute_id = :id',
                                    'params' => [':id' => $model->id],
                                ],
                                'pagination' => false,
                                'sort' => [
                                    'defaultOrder' => 'position ASC',
                                ],
                            ]
                        ),
                        'columns' => [
                            [
                                'class' => 'bootstrap.widgets.TbEditableColumn',
                                'name' => 'value',
                                'editable' => [
                                    'url' => $this->createUrl('/store/attributeStoreBackend/inline'),
                                    'mode' => 'inline',
                                    'params' => [
                                        Yii::app()->getRequest()->csrfTokenName => Yii::app()->getRequest()->getCsrfToken(),
                                    ],
                                ],
                            ],
                            [
                                // 'class' => 'bootstrap.widgets.TbEditableColumn',
                                'name' => 'color',
                                'visible' => $model->isTypeList(),
                                'type' => 'raw',
                                'value' => function($data){
                                    return CHtml::textField('color', $data->color, [
                                        'class' => 'js-color-edit', 
                                        'autocomplete' => 'off',
                                        'data-name' => 'color',
                                        'data-pk' => $data->id,
                                        'data-url' => Yii::app()->createUrl('/store/attributeStoreBackend/inline'),
                                        'style' => 'width: 100px; color: #ссс; background: '.$data->color,
                                    ]);
                                }
                            ],
                            [
                                'class' => 'yupe\widgets\CustomButtonColumn',
                                'template' => '{delete}',
                                'buttons' => [
                                    'delete' => [
                                        'url' => function ($data) {
                                            return Yii::app()->createUrl('/store/attributeStoreBackend/deleteOption',
                                                ['id' => $data->id]);
                                        },
                                        'options' => [
                                            'class' => 'delete btn-sm btn-default',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                ) ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<hr/>

<?php if (!empty($types)): ?>
    <strong><?= Yii::t('StoreModule.store', 'Use in types'); ?></strong>
    <div class="row">
        <?php foreach ($types as $type): ?>
            <div class="form-group">
                <div class="col-sm-7">
                    <div class="checkbox">
                        <label>
                            <?= CHtml::checkBox('types[]', array_key_exists($type->id, $model->getTypes()),
                                ['value' => $type->id]) ?>
                            <?= CHtml::encode($type->name); ?>
                        </label>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script type="text/javascript">
    $(function () {
        $('#add-option-btn').on('click', function (event) {
            event.preventDefault();
            var data = $('#AttributeOption_name').val();
            if (data) {
                $.post($(this).data('url'), {
                    'value': data,
                    '<?= Yii::app()->getRequest()->csrfTokenName;?>': '<?= Yii::app()->getRequest()->getCsrfToken()?>'
                }, function (response) {
                    $.fn.yiiGridView.update('attributes-options-grid');
                }, 'json');
            }
        });

        $('#attribute-type').change(function () {

            if ($.inArray(parseInt($(this).val()), [<?= AttributeStore::TYPE_DROPDOWN;?>, <?= AttributeStore::TYPE_CHECKBOX_LIST;?>]) >= 0) {
                $('#options').removeClass('hidden');
            }
            else {
                $('#options').addClass('hidden');
            }
        });
    });
</script>

<br/><br/>

<?php $this->widget(
    'bootstrap.widgets.TbButton',
    [
        'buttonType' => 'submit',
        'context' => 'primary',
        'label' => $model->getIsNewRecord() ? Yii::t('StoreModule.store',
            'Add attribute and continue') : Yii::t('StoreModule.store', 'Save attribute and continue'),
    ]
); ?>

<?php $this->widget(
    'bootstrap.widgets.TbButton',
    [
        'buttonType' => 'submit',
        'htmlOptions' => ['name' => 'submit-type', 'value' => 'index'],
        'label' => $model->getIsNewRecord() ? Yii::t('StoreModule.store',
            'Add attribute and close') : Yii::t('StoreModule.store', 'Save attribute and close'),
    ]
); ?>

<?php $this->endWidget(); ?>


<?php 
Booster::getBooster()->cs->registerPackage('colorpicker');
Yii::app()->clientScript->registerScript(__FILE__, "
    $('.js-color-edit').each(function(i, e) {
        $(e).colorpicker({'format':'hex'}).on('changeColor', function() {
            var input = $(this),
            url       = input.data('url'),
            pk        = input.data('pk'),
            name      = input.data('name'),
            value     = input.val(),
            scenario  = 'update';

            $.ajax({
                type: 'post',
                url,
                data: {pk, name, value, scenario, YUPE_TOKEN: yupeToken},
                success: function(data) {
                    showNotify($('#attribute-form'), data.result ? 'success' : 'danger', data.data);
                }
            })
        });
    });

    // $(document).on('change', '.js-color-edit', function(e) {
    //     console.log(this);
    //     return false;
    // })
") ?>

<!-- jQuery('#color').colorpicker({'format':'hex'}); -->