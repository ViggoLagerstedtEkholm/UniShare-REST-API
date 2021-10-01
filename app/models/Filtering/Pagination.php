<?php


namespace App\models\Filtering;


class Pagination
{
    private int $start_page_first_result;

    /**
     * @return int
     */
    public function getStartPageFirstResult(): int
    {
        return $this->start_page_first_result;
    }

    /**
     * @param int $start_page_first_result
     */
    public function setStartPageFirstResult(int $start_page_first_result): void
    {
        $this->start_page_first_result = $start_page_first_result;
    }

    /**
     * @return int
     */
    public function getResultsPerPage(): int
    {
        return $this->results_per_page;
    }

    /**
     * @param int $results_per_page
     */
    public function setResultsPerPage(int $results_per_page): void
    {
        $this->results_per_page = $results_per_page;
    }

    /**
     * @return int
     */
    public function getNumberOfPages(): int
    {
        return $this->number_of_pages;
    }

    /**
     * @param int $number_of_pages
     */
    public function setNumberOfPages(int $number_of_pages): void
    {
        $this->number_of_pages = $number_of_pages;
    }
    private int $results_per_page;
    private int $number_of_pages;

}