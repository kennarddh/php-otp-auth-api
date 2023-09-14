## API

-   POST /auth/register
-   { id: string, email: string, username: string, password: string, isVerified: boolean }
-   Respond: {id: string}

-   POST /auth/verify
-   { id: string, OTP: number }
-   Get redis `OTP:id` check if exist, check is same with request body
-   If same update { isVerified:true } to db
-   If not same return 401

-   POST /auth/verify/send
-   { id: string }
-   Verify does user id in body exist
-   OTP: Generate 6 digits random number
-   Store OTP in redis `OTP:user.id`: `OTP`, expire 1 min
-   Send OTP email
-   Respond: {id: string}

-   POST /auth/login
-   { username:string, password:string }
-   If username and password valid if invalid respond 401
-   Check isVerified if false respond 401
-   else respond 200 + JWT

## FE

### Register page

-   Ask Username, password, email
-   Send /auth/register
-   If success send /auth/verify/send
-   Ask for otp then call /auth/verify
-   If success redirect to login page

### Login Page

-   Ask username, password
-   Send /auth/login
-   If success redirect to home and store JWT to localStorage
