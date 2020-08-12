<?php

namespace tsmd\base\rbac\models;

use Yii;
use yii\rbac\Rule;

/**
 * BizRule
 *
 * @property string $name
 * @property string $className

 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class BizRule extends \yii\base\Model
{
    /**
     * @var string name of the rule
     */
    public $name;

    /**
     * @var string Rule classname.
     */
    public $className;

    /**
     * @var Rule
     */
    private $_rule;

    /**
     * Initilaize object
     * @param null|Rule $rule
     * @param array $config
     */
    public function __construct($rule = null, $config = [])
    {
        if ($rule instanceof Rule) {
            $this->_rule = $rule;
            $this->name = $rule->name;
            $this->className = get_class($rule);
        }
        parent::__construct($config);
    }

    /**
     * Get rule
     * @return rule
     */
    public function getRule()
    {
        return $this->_rule;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'className'], 'required'],
            [['className'], 'string'],
            [['className'], 'classExists']
        ];
    }

    /**
     * Validate class exists
     */
    public function classExists()
    {
        if (!class_exists($this->className) || !is_subclass_of($this->className, Rule::class)) {
            $this->addError('className', "Unknown Class: {$this->className}");
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('app', 'Name'),
            'className' => Yii::t('app', 'Class Name'),
        ];
    }

    /**
     * Find model by id
     * @param string $id
     * @return null|static
     */
    public static function find($id)
    {
        $rule = Yii::$app->authManager->getRule($id);
        return $rule ? new static($rule) : null;
    }

    /**
     * Save model to authManager
     * @return boolean
     */
    public function save()
    {
        if ($this->validate()) {
            $manager = Yii::$app->authManager;
            $class = $this->className;
            if ($this->_rule === null) {
                $this->_rule = new $class();
                $isNew = true;
            } else {
                $isNew = false;
                $oldName = $this->_rule->name;
            }
            $this->_rule->name = $this->name;

            if ($isNew) {
                $manager->add($this->_rule);
            } else {
                $manager->update($oldName, $this->_rule);
            }

            return true;
        } else {
            return false;
        }
    }
}
