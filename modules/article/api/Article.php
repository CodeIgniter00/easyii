<?php
namespace yii\easyii\modules\article\api;

use Yii;
use yii\data\ActiveDataProvider;
use yii\widgets\LinkPager;

use yii\easyii\widgets\Colorbox;
use yii\easyii\models\Photo;
use yii\easyii\modules\article\models\Category;
use yii\easyii\modules\article\models\Item;

class Article extends \yii\easyii\components\API
{
    private $_cats;
    private $_items;
    private $_last;

    public function api_cat($id_slug)
    {
        if(!isset($this->_cats[$id_slug])) {
            $this->_cats[$id_slug] = $this->findCategory($id_slug);
        }
        return $this->_cats[$id_slug];
    }

    public function api_cats()
    {
        return Category::getTree();
    }

    public function api_last($limit = 1)
    {
        if($limit === 1 && $this->_last){
            return $this->_last;
        }

        $result = [];
        foreach(Item::find()->status(Item::STATUS_ON)->sort()->limit($limit)->all() as $item){
            $result[] = new ArticleObject($item);
        }

        if($limit > 1){
            return $result;
        }else{
            $this->_last = $result[0];
            return $this->_last;
        }
    }

    public function api_item($id_slug)
    {
        if(!isset($this->_items[$id_slug])) {
            $this->_items[$id_slug] = $this->findItem($id_slug);
        }
        return $this->_items[$id_slug];
    }

    private function findCategory($id_slug)
    {
        $category = Category::find()->where(['or', 'category_id=:id_slug', 'slug=:id_slug'], [':id_slug' => $id_slug])->one();

        return $category ? new CategoryObject($category) : null;
    }

    private function findItem($id_slug)
    {
        if(!($item = Item::find()->where(['or', 'item_id=:id_slug', 'slug=:id_slug'], [':id_slug' => $id_slug])->one())){
            return null;
        }

        $item->updateCounters(['views' => 1]);

        return new ArticleObject($item);
    }
}