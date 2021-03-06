<?php
/**
 * Backend {{ moduleName }} {{ action }} action
 */
{% if action in ['index', 'add', 'edit', 'delete'] %}
class Backend{{ moduleNameSafe }}{{ actionSafe }} extends BackendBaseAction{{ actionSafe }}
{% else %}
class Backend{{ moduleNameSafe }}{{ actionSafe }} extends BackendBaseAction
{% endif %}
{
    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();
{% if action in ['edit', 'delete'] %}

        $this->id = $this->getParameter('id', 'int');
{% endif %}
{% if action == 'delete' %}

        Backend{{ moduleNameSafe }}Model::delete($this->id);
{% endif %}

{% if action == 'index' %}
        $this->loadDataGrids();
{% elseif action in ['add', 'edit'] %}
{% if action == 'edit' %}
        $this->getData();
{% endif %}
        $this->handleForm();
{% endif %}
{% if action != 'delete' %}

        $this->parse();
        $this->display();
{% else %}
        // Redirect
        $this->redirect(BackendModel::createURLForAction('index') . '&report={{ action }}{% if action == 'delete' %}d{% endif %}');
{% endif %}
    }
{% if action == 'index' %}

    /**
     * Loads the datagrids
     */
    protected function loadDataGrids()
    {
        // Create datagrid
        $this->dataGrid = new BackendDataGridArray(Backend{{ moduleNameSafe }}Model::getAll());

        // Add buttons
        $editURL = BackendModel::createURLForAction('edit') . '&id=[id]';
        $this->dataGrid->addColumn(
            'edit',
            null,
            BL::lbl('Edit'),
            $editURL,
            BL::lbl('Edit')
        );
{% if meta %}
        $this->dataGrid->setColumnURL('title', $editURL);
{% endif %}

        // Hide unnecessary columns
        $hiddenColumns = array({% if meta %}'meta_id'{% endif %});
        $this->dataGrid->setColumnsHidden($hiddenColumns);
    }
{% elseif action in ['add', 'edit'] %}
{% if action == 'edit' %}
    /**
     * Get data
     */
    protected function getData()
    {
        $this->record = Backend{{ moduleNameSafe }}Model::get($this->id);

        // Validate
        if (empty($this->record)) {
            $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
        }
    }
{% endif %}

    /**
     * Handle form
     */
    protected function handleForm()
    {
        // Create the form
        $form = new BackendForm('{{ action }}');

        // Add fields
{% if meta %}
        $fieldTitle = $form->addText('title', {% if action == 'edit' %}$this->record['title']{% else %}null{% endif %}, 255, 'inputText title', 'inputTextError title');
{% endif %}
{% if tags and action in ['add', 'edit'] %}
        $fieldTags = $form->addText('tags', {% if action == 'edit' %}$this->record['tags']{% else %}null{% endif %}, null, 'inputText tagBox', 'inputTextError tagBox');
{% endif %}
{% if meta %}

        // Meta
{% if action == 'add' %}
        $meta = new BackendMeta($form, null, 'title', true);
{% elseif action == 'edit' %}
        $meta = new BackendMeta($form, $this->record['meta_id'], 'title', true);

        // Set callback for generating a unique URL
        $meta->setUrlCallback('Backend{{ moduleNameSafe }}Model', 'getURL', array($this->record['id']));
{% endif %}
{% endif %}

        // Submitted?
        if ($form->isSubmitted()) {
            // Check fields
{% if meta %}
            $meta->validate();
            $fieldTitle->isFilled(BL::err('FieldIsRequired'));
{% endif %}

            // Correct?
            if ($form->isCorrect()) {
                // Build item
                $item = array();
{% if action == 'edit' %}
                $item['id'] = $this->id;
{% endif %}
{% if meta %}
                $item['title'] = $fieldTitle->getValue();
                $item['meta_id'] = $meta->save();
{% endif %}
{% if tags %}

                $tags = $fieldTags->getValue();
{% endif %}

                // Save
{% if action in ['add', 'edit'] %}
                {% if action == 'add' %}$id = {% endif %}Backend{{ moduleNameSafe }}Model::{% if action == 'add' %}create{% endif %}{% if action == 'edit' %}update{% endif %}($item{% if tags %}, $tags{% endif %});
{% endif %}

                // Redirect
                $redirectURL = BackendModel::createURLForAction('index');
{% if action in ['add', 'edit'] %}
                $redirectURL .= '&highlight=row-' . {% if action == 'add' %}$id{% elseif action == 'edit' %}$this->record['id']{% endif %};
{% endif %}
                $redirectURL .= '&report={{ action }}ed';
                $this->redirect($redirectURL);
            }
        }

        $form->parse($this->tpl);
    }
{% endif %}
{% if action != 'delete' %}

    /**
     * Parse method
     */
    protected function parse()
    {
        parent::parse();
{% if action == 'index' %}
        $this->tpl->assign('dataGrid', ($this->dataGrid->getNumResults() != 0) ? $this->dataGrid->getContent() : false);
{% elseif action in ['add', 'edit'] and meta %}
        // Get url
        $url = BackendModel::getURLForBlock($this->URL->getModule(), 'detail');
        $url404 = BackendModel::getURL(404);

        // Parse additional variables
        if ($url404 != $url) {
            $this->tpl->assign('detailURL', SITE_URL . $url);
        }
{% if action == 'edit' %}

        $this->tpl->assign('item', $this->record);
{% endif %}
{% endif %}
    }
{% endif %}
}
