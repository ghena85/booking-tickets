<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Row;
use App\Models\Col;
use App\Models\Schema;
use App\Models\Ticket;

class SchemaService
{

    /**
     * @var Schema
     */
    private Schema $schema;

    /**
     * @var array
     */
    private array $data;

    /**
     * @var array
     */
    private array $cartColIds = [];

    /**
     * @var array
     */
    private array $reservedColIds = [];

    /**
     * @var Event
     */
    private Event $event;

    /**
     * @param Schema    $schema
     * @param Event $event
     *
     * @return array
     */
    public function generateRowsData(Schema $schema, Event $event) : array
    {
        $this->schema = $schema;
        $this->event = $event;

        $this->fillBusyIds();
        $this->fillCartIds();
        $this->fillRows();

        return $this->data;
    }

    private function fillBusyIds() : void
    {
        $paid     = $this->event->tickets->where('status','paid')->pluck('col_id')->toArray();
        $reserved = $this->event->tickets->where('status','reserved')->pluck('col_id')->toArray();
        $this->reservedColIds = array_merge($paid,$reserved);
    }

    private function fillCartIds() : void
    {
        foreach (\Cart::getContent()->toArray() as $key => $value) {
            if ($value['attributes']['event_id'] == $this->event->id && ! in_array($key, $this->cartColIds)) {
                $this->cartColIds[] = $key;
            }
        }
    }

    private function fillRows()
    {
        // rows
        $this->schema->rows->filter(function (Row $row) {
            return ! $row->on_balcony && ! $row->on_loggia;
        })->each(function (Row $row) {
            collect($row->cols)->each(function (Col $col) use ($row) {
                $colData = $this->setClass($col->toArray());
                $colData['row'] = $row->row;
                $this->data['rows']['items'][$row->id]['color'] = $row->color->name;
                if ($col->on_left) {
                    $this->data['rows']['items'][$row->id]['data']['on_left'][] = $colData;
                } else {
                    $this->data['rows']['items'][$row->id]['data']['on_right'][] = $colData;
                }
            });
        });

        // loggia
        $this->schema->rows->filter(function (Row $row) {
            return $row->on_loggia && ! $row->on_balcony;
        })->map(function (Row $row) {
            $this->data['rows']['loggia'][$row->id]['color'] = $row->color->name;
            if ($row->on_left) {
                $this->data['rows']['loggia'][$row->id]['data']['on_left'] = $row->cols->map(function (Col $col) use ($row) {
                    $colData = $col->toArray();
                    $colData['row'] = $row->row;
                    return $this->setClass($colData);
                })->toArray();
            } else {
                $this->data['rows']['loggia'][$row->id]['data']['on_right'] = $row->cols->map(function (Col $col) use ($row) {
                    $colData = $col->toArray();
                    $colData['row'] = $row->row;
                    return $this->setClass($colData);
                })->toArray();
            }
        });
    }

    /**
     * @param array $colData
     *
     * @return array
     */
    private function setClass(array $colData) : array
    {
        $colData['class'] = '';
        if (in_array($colData['id'], $this->cartColIds)) {
            $colData['class'] = 'active';

        } elseif (in_array($colData['id'], $this->reservedColIds)) {
            $colData['class'] = 'busy';

            $order = Ticket::query()
                ->where('event_id', $this->event->id)
                ->where('col_id', $colData['id'])
                ->first()->order;
            if($order){

                $colData['name'] = $order->first_name . ' '
                    . $order->last_name . ' '
                    . $order->phone . ' '
                    . $order->email;
            } else {
                $colData['name'] = 'Reserved';
            }
        }


        $colData['active'] = false;

        return $colData;
    }
}
