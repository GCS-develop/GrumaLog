namespace app\components;

use yii\db\Query;

class PivotQuery extends Query
{
    public function pivot($aggregation, $columns)
    {
        $this->select = [];
        foreach ($columns as $column) {
            $this->select[] = "COALESCE([$column], 0) AS [$column]";
        }

        $this->addSelect($aggregation);

        return $this;
    }
}
