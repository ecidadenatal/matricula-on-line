<?php

class EstruturaCallback implements EstruturaPluginCallback {

  /**
   * Funчуo chamada antes de rodar o sql que cria a estrutura do plugin na base de dados
   * @param  Database $oDatabase
   * @throws Exception
   */
  public function beforeInstall(Database $oDatabase) {}

  /**
   * Funчуo chamada depois de rodar o sql que cria a estrutura do plugin na base de dados
   * @param  Database $oDatabase
   * @throws Exception
   */
  public function afterInstall(Database $oDatabase) {}

  /**
   * Funчуo chamada antes de rodar o sql que remove a estrutura do plugin da base de dados
   * @param  Database $oDatabase
   * @throws Exception
   */
  public function beforeUninstall(Database $oDatabase) {}

  /**
   * Funчуo chamada depois de rodar o sql que remove a estrutura do plugin da base de dados
   * @throws Exception
   * @param  Database $oDatabase
   */
  public function afterUninstall(Database $oDatabase) {}
}