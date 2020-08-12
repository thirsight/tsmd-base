<?php

namespace tsmd\base\rbac\models;

use Yii;
use yii\data\ArrayDataProvider;

/**
 * Description of BizRule
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class BizRuleSearch extends BizRule
{
    public function rules()
    {
        return [
            [['name'], 'trim'],
            [['name'], 'safe'],
        ];
    }

    /**
     * Search BizRule
     * @param array $params
     * @param string $formName
     * @return \yii\data\ArrayDataProvider
     */
    public function search($params, $formName = null)
    {
        $models = [];
        $included = !($this->load($params, $formName) && $this->validate() && $this->name !== '');
        foreach (Yii::$app->authManager->getRules() as $name => $rule) {
            if ($name != RouteRule::RULE_NAME && ($included || stripos($rule->name, $this->name) !== false)) {
                $models[$name] = new BizRule($rule);
            }
        }

        return new ArrayDataProvider([
            'allModels' => $models,
        ]);
    }
}
