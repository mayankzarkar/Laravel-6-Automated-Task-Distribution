# Automated Pipeline Task Management

About this module:
This module can handle all the manual work of hiring process, like filtering the candidate, take a required action for a specific candidate, send a reminder to complete the process, auto move to next stage, send offer letter, etc.
Those things can be done by writing a single query from UI, for example:

![Image](./data/Automation1.png?q=1)
![Image](./data/Automation2.png?q=2)
![Image](./data/Automation3.png?q=3)
![Image](./data/Automation4.png?q=4)
![Image](./data/Automation5.png?q=5)
![Image](./data/Automation6.png?q=6)
![Image](./data/Automation.png?q=7)

By simple writing the above queries we reduced a lot of the time to handle those things manually.
I shared a small set of codes of this module, and here are some backend related points:
1. I've used `Console/Commands` to handle some CRON work like sending the reminders to users and stuffs like that.
2. To handle the process in background I've used the Redis queue, so we have background `Jobs` to handle those actions.
3. For tracking every change of `Entities / Model` we are using the `Observers`.
4. To separate the code, we are using the `Events & Listeners` for handling the work in background.
5. Using the SOLID rules, we added a `Traits` to handle only single responsibility work.
6. For handling the common functionality we are using the `Foundation/collection`. 
7. For handling the API's we are using the `Http/Controllers`.
8. To handle the validation we are using the `Http/Requests`.

Thanks
