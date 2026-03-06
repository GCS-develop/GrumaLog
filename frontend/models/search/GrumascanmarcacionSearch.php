<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Grumascanmarcacion;
use yii\db\Query;

class GrumascanmarcacionSearch extends Grumascanmarcacion
{
    // ✅ permitir arrays (Select2 multiple)
    public $idbodega;
    public $ubicacion;
    public $seccion;
    public $estado;

    public function rules()
    {
        return [
            [['id', 'created_by', 'updated_by', 'idconteo'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['idbodega', 'ubicacion', 'seccion', 'estado'], 'safe'], // ✅ arrays
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $id = null)
    {
        $query = Grumascanmarcacion::find()->alias('gsm');

        if ($id !== null) {
            $query->andWhere(['gsm.id' => (int)$id]);
        }

        $query->join('LEFT JOIN', 'grumascanconteo gsc', 'gsm.id = gsc.idmarcacion');
        $query->join('LEFT JOIN', 'grumascanestado gse', 'gse.id = gsc.idestado');

        $query->select([
            'gsm.*',
            'gsc.id AS idconteo',
            'gse.nombre AS estado',
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'gsm.id' => $this->id,
            'gsm.created_at' => $this->created_at,
            'gsm.created_by' => $this->created_by,
            'gsm.updated_at' => $this->updated_at,
            'gsm.updated_by' => $this->updated_by,
            'gsc.id' => $this->idconteo,
        ]);

        // ✅ bodegas multiple IN
        if (!empty($this->idbodega)) {
            $bodegas = array_values(array_filter((array)$this->idbodega, fn($v) => (string)$v !== ''));
            if ($bodegas) {
                $query->andWhere(['in', 'gsm.idbodega', array_map('intval', $bodegas)]);
            }
        }

        // ✅ ubicaciones multiple IN (exact)
        if (!empty($this->ubicacion)) {
            $ubic = array_values(array_filter((array)$this->ubicacion, fn($v) => trim((string)$v) !== ''));
            if ($ubic) {
                $query->andWhere(['in', 'gsm.ubicacion', $ubic]);
            }
        }

        // ✅ secciones multiple IN (exact)
        if (!empty($this->seccion)) {
            $sec = array_values(array_filter((array)$this->seccion, fn($v) => trim((string)$v) !== ''));
            if ($sec) {
                $query->andWhere(['in', 'gsm.seccion', $sec]);
            }
        }

        // ✅ estado multiple con "3 = Sin conteo"
        if (!empty($this->estado)) {
            $estados = (array)$this->estado;

            $incluyeSinConteo = in_array('3', $estados, true);
            $idsEstados = array_values(array_filter($estados, fn($v) => (string)$v !== '3' && (string)$v !== ''));

            if ($incluyeSinConteo && !empty($idsEstados)) {
                $query->andWhere([
                    'or',
                    ['gsc.id' => null],
                    ['in', 'gse.id', array_map('intval', $idsEstados)],
                ]);
            } elseif ($incluyeSinConteo) {
                $query->andWhere(['gsc.id' => null]);
            } else {
                $query->andWhere(['in', 'gse.id', array_map('intval', $idsEstados)]);
            }
        }

        return $dataProvider;
    }

    /**
     * Listas para Select2 (ajusta si tu tabla/modelo de bodega tiene otro nombre)
     */
    public function getListaBodegas(): array
    {
        // ✅ AJUSTA: si tienes tabla bodega con id/nombre:
        // return \yii\helpers\ArrayHelper::map(\frontend\models\Bodega::find()->orderBy('nombre')->all(), 'id', 'nombre');

        // Fallback genérico (si no tienes modelo Bodega a mano)
        return (new Query())
            ->from('bodegas')
            ->select(['nombre', 'id'])
            ->orderBy(['nombre' => SORT_ASC])
            ->indexBy('id')
            ->column();
    }

    public function getListaUbicaciones(): array
    {
        $rows = (new Query())
            ->from(['gsm' => 'grumascanmarcacion'])
            ->select(['ubicacion'])
            ->distinct()
            ->where(['not', ['ubicacion' => null]])
            ->andWhere(['<>', 'ubicacion', ''])
            ->orderBy(['ubicacion' => SORT_ASC])
            ->column();

        // value => value
        return array_combine($rows, $rows) ?: [];
    }

    public function getListaSecciones(): array
    {
        $rows = (new Query())
            ->from(['gsm' => 'grumascanmarcacion'])
            ->select(['seccion'])
            ->distinct()
            ->where(['not', ['seccion' => null]])
            ->andWhere(['<>', 'seccion', ''])
            ->orderBy(['seccion' => SORT_ASC])
            ->column();

        return array_combine($rows, $rows) ?: [];
    }
}
