<?php
namespace LernmodulePlugin;

/**
 * @template T of SORM
 */
trait SORMCrudCallbacksTrait
{
    /**
     * @param T $sorm
     * @return T
     */
    protected function beforeStore(SORM $sorm, array $data): SORM
    {
        return $sorm;
    }

    /**
     * @param T $sorm
     * @return T
     */
    protected function afterStore(SORM $sorm, array $data): SORM
    {
        return $sorm;
    }
}
