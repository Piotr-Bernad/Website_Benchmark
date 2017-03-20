# Website_Benchmark
Web app that can benchmark loading time of the website in comparison to the other
websites (check how fast is the website's loading time in comparison to other competitors).

## Usage instruction

To run type the command:
    php compare -w website_url -c "competitor1_url|competitor2_url|competitor3_url"

<<<<<<< HEAD
## PARAMETERS
*-w [website_url]    - url of site being tested
*-c [competitor_url] - url of competitor, if more than one competitor needed then must be separate by '|' sign and be in quotes.

## EXAMPLES

With specified competitors:
* php compare -c www.apple.com -w "www.google.com|www.facebook.com|www.gazeta.pl"

or with default competitors:
* php compare -c www.apple.com