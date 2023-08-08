# Run
```bash
cd tests/sitespeed

docker run --rm -v "$(pwd):/sitespeed.io" sitespeedio/sitespeed.io --axe.
enable --browsertime.iterations 1 --html.showScript --budget.configPath
budget.json --budget.output junit --basicAuth klant@KlantLogin --multi test.js
```

# Dashboarding (not implemented)
https://www.sitespeed.io/documentation/sitespeed.io/performance-dashboard/
Example: https://dashboard.sitespeed.io/d/3zStduRGk/welcome-to-sitespeed-io

# Compare (not implemented)
Info: https://github.com/sitespeedio/compare#compare-har-files
Online: https://compare.sitespeed.io/
