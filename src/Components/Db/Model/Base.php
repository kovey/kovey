<?php
/**
 *
 * @description 对应表
 *
 * @package    Kovey\Components\Db\Model 
 *
 * @time        2020-01-19 17:55:12
 *
 * @author      kovey
 */
namespace Kovey\Components\Db\Model;

use Kovey\Components\Db\DbInterface;
use Kovey\Components\Db\Sql\Select;
use Kovey\Components\Db\Sql\Where;

abstract class Base extends ShardingBase
{
    /**
     * @description 插入数据
     *
     * @param Array $data
     *
     * @param DbInterface $db
     *
     * @return int
     *
     * @throws Exception
     */
    public function insert(Array $data, DbInterface $db, $shardingKey = -1)
    {
        return parent::insert($data, $db, $shardingKey);
    }

    /**
     * @description 更新数据
     *
     * @param Array $data
     *
     * @param Array $condition
     *
     * @param DbInterface $db
     *
     * @return int
     *
     * @throws Exception
     */
    public function update(Array $data, Array $condition, DbInterface $db, $shardingKey = -1)
    {
        return parent::update($data, $condition, $db, $shardingKey);
    }

    /**
     * @description 获取一行数据
     *
     * @param Array $condition
     *
     * @param Array $columns
     *
     * @param DbInterface $db
     *
     * @return Array
     *
     * @throws Exception
     */
    public function fetchRow(Array $condition, Array $columns, DbInterface $db, $shardingKey = -1)
    {
        return parent::fetchRow($condition, $columns, $db, $shardingKey);
    }

    /**
     * @description 获取所有数据
     *
     * @param Array $condition
     *
     * @param Array  $columns
     *
     * @param DbInterface $db
     *
     * @return Array
     *
     * @throws Exception
     */
    public function fetchAll(Array $condition, Array $columns, DbInterface $db, $shardingKey = -1)
    {
        return parent::fetchAll($condition, $columns, $db, $shardingKey);
    }

    /**
     * @description 批量插入
     *
     * @param Array $rows
     *
     * @param DbInterface $db
     *
     * @return bool
     *
     * @throws Exception
     */
    public function batchInsert(Array $rows, DbInterface $db, $shardingKey = -1)
    {
        return parent::batchInsert($rows, $db, $shardingKey);
    }

    /**
     * @description 删除数据
     *
     * @param Array $data
     *
     * @param Array $condition
     *
     * @param DbInterface $db
     *
     * @return int
     *
     * @throws Exception
     */
    public function delete(Array $condition, DbInterface $db, $shardingKey = -1)
    {
        return parent::delete($condition, $db, $shardingKey);
    }

    public function fetchByPage(Array $condition, Array $columns, int $page, int $pageSize, DbInterface $db, string $tableAs = '', string $order = '', string $group = '', Array $join = array())
    {
        $select = new Select($this->getTableName(), $tableAs);
        $totalSelect = new Select($this->getTableName(), $tableAs);
        $select->columns($columns)
               ->limit($page, $pageSize);

        $totalSelect->columns(array('count' => 'count(1)'));
        if (!empty($order)) {
            $select->order($order);
        }
        if (!empty($group)) {
            $select->group($group);
        }
        if (!empty($condition)) {
            $select->where($condition);
            $totalSelect->where($condition);
        }

        if (!empty($join)) {
            foreach ($join as $type => $info) {
                if (empty($info)) {
                    continue;
                }

                if ($type === 'LEFT_JOIN') {
                    $select->leftJoin($info['table'], $info['on'], $info['columns']);
                    $totalSelect->leftJoin($info['table'], $info['on']);
                    continue;
                }

                if ($type === 'INNER_JOIN') {
                    $select->innerJoin($info['table'], $info['on'], $info['columns']);
                    $totalSelect->innerJoin($info['table'], $info['on']);
                    continue;
                }

                if ($type === 'RIGHT_JOIN') {
                    $select->rightJoin($info['table'], $info['on'], $info['columns']);
                    $totalSelect->rightJoin($info['table'], $info['on']);
                    continue;
                }
            }
        }

        $rows = $db->select($select);
        $total = $db->select($totalSelect, Select::SINGLE);
        $totalCount = intval($total['count']);
        return array(
            'totalCount' => $totalCount,
            'totalPage' => ceil($totalCount / $pageSize),
            'list' => $rows
        );
    }
}
