<?php

use yupe\components\controllers\FrontController;

/**
 * Class CategoryController
 */
class CategoryController extends FrontController
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var StoreCategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var AttributeFilter
     */
    protected $attributeFilter;

    /**
     *
     */
    public function init()
    {
        parent::init();
        $this->productRepository = Yii::app()->getComponent('productRepository');
        $this->attributeFilter = Yii::app()->getComponent('attributesFilter');
        $this->categoryRepository = Yii::app()->getComponent('categoryRepository');
    }

    /**
     *
     */
    public function actionIndex()
    {
        $this->render(
            'index',
            [
                'dataProvider' => $this->categoryRepository->getAllDataProvider(),
            ]
        );
    }

    /**
     * @param $path
     * @throws CHttpException
     */
    public function actionView($path)
    {
        $category = $this->categoryRepository->getByAlias($path);
        
        if (null === $category) {
            throw new CHttpException(404);
        }
        

        $model = new StoreCategory();
        $criteria = new CDbCriteria();
        $criteria->scopes = ['published'];
        $criteria->addInCondition('t.parent_id', '');
        $categories = $model->findAll($criteria);

        

        $typesSearchParam = $this->attributeFilter->getTypeAttributesForSearchFromQuery(Yii::app()->getRequest());

        $mainSearchParam = $this->attributeFilter->getMainAttributesForSearchFromQuery(
            Yii::app()->getRequest(),
            [
                AttributeFilter::MAIN_SEARCH_PARAM_CATEGORY => Yii::app()->getRequest()->getQuery(
                    'category',
                    [$category->id]
                ),
            ]
        );

        if (!empty($mainSearchParam) || !empty($typesSearchParam)) {
            $data = $this->productRepository->getByFilter($mainSearchParam, $typesSearchParam);
        } else {
            $data = $this->productRepository->getListForCategory($category);
        }

        $this->render(
            $category->view ?: 'view',
            [
                'dataProvider' => $data,
                'category' => $category,
                'categories' => $categories,
            ]
        );
    }
    
    public function actionSearch()
    {
        
        header('Content-type: application/json');
        
        $q = Yii::app()->request->getParam('q');
        
        $products = $this->productRepository->search($q);
        
        $result = [];
        
        foreach($products as $product) {
            $result[] = ['text' => $product->name, 'id' => Yii::app()->createUrl('/product/'.$product->slug)];
        }
        
        
        echo CJSON::encode(['results' => $result]);
        Yii::app()->end();
    }

    // Проверка наличия города в url
    public function showBottomText()
    {
        $current_url = explode('/', $_SERVER['REQUEST_URI']);
        $slugs = Yii::app()->getDb()->createCommand()
            ->setFetchMode(PDO::FETCH_COLUMN, 0)
            ->from('{{city}}')
            ->select('slug')
            ->queryAll();
        if (in_array($current_url[1], $slugs)) {
            return true;
        }
    }
}
