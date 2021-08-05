
[[ * Notes or to do's                                  ]]
[[ - Removed or relocated components to different repo ]]
[[ + Relocated classes                                 ]]

* Check methods on session classes to make sure non are missing and to ensue they have a similar/exact api.

* Decouple storage mechanism from Component\Storage\Storage.
  Require data adapter (DatabaseStorage|FileSystemStorage) to be constructed beforehand and passed in first argument.

* Add a `DatabaseListener` as a logger listener option. LynkCMS\Component\Logger\Listener\DatabaseListener

----------------------------------------------------------------------------------------------------
