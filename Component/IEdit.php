<?php

namespace RPI\Framework\Component;

/**
 * Marks a component as editable. This allows C(R)UD operations to be performed
 * on a component. Read is not required as it is part of the default component
 * rendition.
 */
interface IEdit
{
    /**
     * Create a data item
     * @param $bind     string      String which defines the path to the data item being created
     * @param $content  mixed       String or markup content
     * @return False on failure (or if there is no implementation required) or True for success
     */
    public function create($bind, $data);

    /**
     * Update a data item
     * @param $bind     string      String which defines the path to the data item being created
     * @param $content  mixed       String or markup content
     * @return False on failure (or if there is no implementation required) or True for success
     */
    public function update($bind, $content);

    /**
     * Delete a data item
     * @param $bind     string      String which defines the path to the data item being created
     * @return False on failure (or if there is no implementation required) or True for success
     */
    public function delete($bind);
}
