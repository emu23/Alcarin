<?php

namespace Admin\GameObject\Extension;

class OrbisGateways extends \Core\GameObject
{
    const COLLECTION = 'map.gateways';

    public function find($grouped = true)
    {
        $gateways = $this->mongo()->{static::COLLECTION}->find()->sort(['group' => 1, 'name'=> 1]);
        if($grouped) {
            $gateways = $gateways->toArray();
            $grouped_gateways = [];
            foreach($gateways as $gateway) {
                $_group = empty($gateway['group']) ? 0 : $gateway['group'];
                if(empty($grouped_gateways[$_group])) {
                    $grouped_gateways[$_group] = [];
                }
                $grouped_gateways[$_group] []= $gateway;
            }
            $gateways = $grouped_gateways;
        }

        return $gateways;
    }

    public function rename_group($old_name, $new_name)
    {
        $all = $this->find();

        if(empty($all[$old_name])) {
            return "Can not find group '$old_name'.";
        }
        if(!empty($all[$new_name])) {
            return "Group with name '$new_name' exists.";
        }

        foreach($all[$old_name] as $gateway) {
            $gateway['group'] = $new_name;
            $this->mongo()->{static::COLLECTION}->updateById($gateway['_id'], $gateway);
        }

        return true;
    }

    public function delete($gateway_id)
    {
        return $this->mongo()->{static::COLLECTION}->removeById($gateway_id);
    }

    public function delete_group($group_name)
    {
        if($group_name === 0 || $group_name == 'Ungrouped') return false;
        $all = $this->find();

        if(empty($all[$group_name])) return false;

        foreach($all[$group_name] as $gateway) {
            $gateway['group'] = 0;
            $this->mongo()->{static::COLLECTION}->updateById($gateway['_id'], $gateway);
        }

        return true;
    }

    public function update($id, $name, $description, $x, $y, $group = null)
    {
        if($group == 'Ungrouped') $group = 0;

        return $this->mongo()->{static::COLLECTION}->updateById($id, [
            'name'        => $name,
            'description' => $description,
            'x'           => $x,
            'y'           => $y,
            'group'       => $group,
        ]);
    }

    public function insert($name, $description, $x, $y, $group = null)
    {
        if($group == 'Ungrouped') $group = 0;

        $data = [
            'name'        => $name,
            'description' => $description,
            'x'           => $x,
            'y'           => $y,
            'group'       => $group === null ? 0 : $group,
        ];
        $result = $this->mongo()->{static::COLLECTION}->insert($data);
        if($result) return $data['_id']->{'$id'};
        return false;
    }
}