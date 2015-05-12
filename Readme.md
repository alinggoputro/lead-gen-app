-------------------------
TASK: Lead Generation Tool Prepared for ConductHQ
Prepared by: Arya Linggoputro on 12 May 2015
-------------------------

The solution build using AngularJS, Bootstrap, PHP and MYSQL. 


------------------
Logic of Equal Distribution
------------------

The logic was written in the api.php  and the explanation of the solution is below:
1. Get the total leads 
2. Get the total active agents
3. Get the offset by dividing number of leads by agents
4. Use array_splice to equally distribute
5. Update the agent_id field for corresponding leads 
6. Sending email out to both corresponding agents and leads


The function of _autoDistributeAgents() will be trigger everytime there is a new lead or removal of lead and as for activated, deactivated and reactivated agent it could be inserted to the function (if applicable) alternatively :
- setup a cronjob
- trigger the function when someone visit the page


