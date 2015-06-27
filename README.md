# WhereConference
API para busca de conferências nacionais e internacionais

#Configuração
Requisito para configuração da API:
- Banco de dados mysql instalado
- Servidor Apache 2.4.4
- PHP 5.1~

#Passos para build do serviço
-Executar o dump da base dados no mysql, o arquivo está em BD/DumpBaseDados.sql
-Executar o script para popular a base, o arquivo está em BD/PopulateDB.sql
-Configurar o arquivo .htaccess
-Seguir os passos da documentação em Documentation/WebserviceAPISpecificationDocTemplate.docx

#URLs Base
<base_url>conference/conferencias
<base_url>conference/categorias
<base_url>conference/areas
