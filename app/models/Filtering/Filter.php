<?php

namespace App\models\Filtering;

class Filter
{
    private ?string $search;
    private string $filterOption;
    private string $filterOrder;
    private int $results_per_page_count;
    private int $page;

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    /**
     * @return string|null
     */
    public function getSearch(): string|null
    {
        return $this->search;
    }

    /**
     * @param string|null $search
     */
    public function setSearch(?string $search): void
    {
        $this->search = $search;
    }

    /**
     * @return string
     */
    public function getFilterOption(): string
    {
        return $this->filterOption;
    }

    /**
     * @param string $filterOption
     */
    public function setFilterOption(string $filterOption): void
    {
        $this->filterOption = $filterOption;
    }

    /**
     * @return string
     */
    public function getFilterOrder(): string
    {
        return $this->filterOrder;
    }

    /**
     * @param string $filterOrder
     */
    public function setFilterOrder(string $filterOrder): void
    {
        $this->filterOrder = $filterOrder;
    }

    /**
     * @return int
     */
    public function getResultsPerPageCount(): int
    {
        return $this->results_per_page_count;
    }

    /**
     * @param int $results_per_page_count
     */
    public function setResultsPerPageCount(int $results_per_page_count): void
    {
        $this->results_per_page_count = $results_per_page_count;
    }
}