### Question-1 
##### How would you scale this system to support 100,000 orders per day? What would become the first bottleneck?
The first bottleneck would likely be the relational database because every order modifies shared inventory and order data.

As traffic grows, contention on the database becomes the limiting factor, especially when multiple customers try to purchase the same product simultaneously. \
there are multiple workarounds around this such as 
- use replices for ready heavy operations we can have a master read/write database and add replicase around it
- this system can scale very well horizontally we can add multiple instances of our laravel application behind a load-balancer server
- also we can dispatch multiple queue workers to further speed things up
- Redis can be used for: queues, caching, rate limiting,idempotency keys This reduces pressure on MySQL.
- proper indexing can surely improve performance alot
- database connection pooling can help for very high concurrency like 2000 requests/sec 
- If writes eventually become too high, partitioning or sharding could be considered
### Question-2
##### What do you consider the biggest security risk in this system, and how would you mitigate it?
The most valuable asset in this system is the inventory of account credentials. Since these credentials must later be delivered to customers, they cannot be stored as hashes. I would encrypt them at rest, validate payment webhooks using HMAC signatures, and ensure all communication occurs over HTTPS. Together, these measures significantly reduce the risk of credential theft or fraudulent account delivery.

I would log:

- payment confirmations
- account deliveries
- failed webhook validations
- administrative actions

to help detect suspicious activity.

Secrets such as:

- payment gateway secret
- application key
- database credentials

should never be committed to Git and should be stored in environment variables or a secure secret manager.

Encrypt credentials: \
Unlike user passwords, these credentials must be recovered later to deliver them. \
Therefore they should not be hashed. \
Instead I would encrypt them using Laravel's encryption facilities before storing them.