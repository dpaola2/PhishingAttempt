One of our student advisors at Bloc received a phishing email today. I thought I'd embark down the rabbit hole to discover who was behind it and show my work.

Here are the steps I took (my scratch pad is info.md):

1. Open the attached file in my editor (Request for Quotation.html)
2. Notice it had one script tag with base-64 encoded data. Put that data into the `base64` file, Decode that and store it in decoded.html (using the `base64` OS X CLI)
3. Open decoded.html, notice it has encoded data that is unescaped and then passed to `document.write`, which executes the code immediately
4. create javascript_decode.html containing the encoded data and log it to the console. open that doc in my browser, save the console logged data as `unescaped.html`
5. Find the form POST location in `unescaped.html` and notice it POSTs to a `http://www.kamesinvestmentint.com/wp/xplore/babajesus/mail.php`
6. Perform dig & whois lookups for kamesinvestmentint.com and the other URL found in the unescaped file, millwallcu.com.
7. Use `wget -mk <url>` to pull down a mirror of kamesinvestmentint.com
8. Open the mail.php file -- it simply redirects to the index.html file in the same directory, so i'll stop investigating and go back to the WHOIS data

Based on the WHOIS data and rudimentary google searches, this is a server hosted in Bauchi, Nigeria, both domains registered to someone named Umar Yusuf. His address is in the info.md file and the various whois PDFs in this repo.

This Umar Yusuf could be the person responsible, or simply an unfortunate victim himself (his server could be compromised).

A fun rabbit hole.
