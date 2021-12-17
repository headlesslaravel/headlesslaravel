<?php

namespace HeadlessLaravel\Metrics\Concerns;

trait Outputs
{
    public function asChart(): self
    {
        $this->as = function($values) {
            return [
                'labels' => $values->pluck('key'),
                'datasets' => [
                    [
                        'label' => 'Some Label',
                        'backgroundColor' => '#000',
                        'data' => $values->pluck('value')
                    ]
                ],
            ];
        };

        return $this;
    }

    public function asTable(): self
    {
        $this->as = function($values) {
            // | Month | Count |
            // | Day | Max |
            $html = "<table><tr><th>{$this->interval}</th><th>{$this->aggregate}</th></tr>";

            foreach($values as $row) {
                $html .= "<tr><td>{$row['key']}</td><td>{$row['value']}</td></tr>";
            }

            // TODO: add footer if count or sum
//            $html .= "<tr><td>Total {$this->aggregate}</td><td>{$values->sum($this->column)}</td></tr>";

            return $html .= '</table>';
        };

        return $this;
    }

    public function asCsv()
    {
        $this->as = function($values) {
            $f = fopen('php://memory', 'r+');

            fputcsv($f, [$this->interval, 'value']);

            foreach ($values as $row) {
                fputcsv($f, [$row['key'], $row['value']]);
            }

            rewind($f);

            return stream_get_contents($f);
        };

        return $this;
    }
}
