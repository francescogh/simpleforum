<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Thread;

/**
 * ThreadSearch represents the model behind the search form about `app\models\Thread`.
 */
class ThreadSearch extends Thread
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'author.username'], 'safe'],
        ];
    }
    
	public function attributes()
	{
	    // add related fields to searchable attributes
	    return ['title', 'author.username'];
	}

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Thread::find();
        $query->joinWith('author AS author');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $dataProvider->sort->attributes['author.username'] = [
    		'asc' => ['author.username' => SORT_ASC],
    		'desc' => ['author.username' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'author.username', $this->getAttribute('author.username')])
            ->addOrderBy('creationDate DESC');

        return $dataProvider;
    }
}
