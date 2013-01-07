function addEngine(name,ext,cat,url)
{
  if ((typeof window.sidebar == "object") && (typeof
  window.sidebar.addSearchEngine == "function"))
  {
    window.sidebar.addSearchEngine(
      url + "/uploads/tagmemo/"+name+".src",
      url + "/modules/tagmemo/images/"+name+"."+ext,
      name,
      cat );
  }
}