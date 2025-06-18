// Function to handle different replies
function showReply(replyType) {
    const responseBox = document.getElementById('response-message');
    const botReplyBox = document.getElementById('bot-reply');
    
    // Show loading spinner
    responseBox.innerHTML = '<div class="loading-spinner"></div>';
    botReplyBox.style.display = 'flex'; // Show reply box immediately for loading effect
  
    // Simulate a delay for loading the response
    setTimeout(() => {
      // Replace loading spinner with bot reply
      let message = '';
  
      switch (replyType) {
        case 'shop':
          message = 'আপনি দোকান খোলার জন্য প্রথমে আমাদের পোর্টালে রেজিস্ট্রেশন করতে হবে।';
          break;
        case 'delivery':
          message = 'ডেলিভারি ম্যান হতে চাইলে, আপনি আমাদের সিস্টেমে সাইন আপ করে আপনার স্থানীয় অঞ্চলে কাজ করতে পারেন।';
          break;
        case 'buy':
          message = 'আপনি পণ্য ক্রয় করতে চান, তাহলে আমাদের পণ্য তালিকা থেকে আপনার পছন্দের পণ্য নির্বাচন করুন।';
          break;
        case 'coin':
          message = 'কয়েন ব্যবহার করে ডিসকাউন্ট পেতে, আপনার অ্যাকাউন্টে কয়েন যোগ করতে হবে।';
          break;
        case 'report':
          message = 'আপনি একটি দোকান রিপোর্ট করতে চান, দয়া করে সংশ্লিষ্ট দোকানের তথ্য প্রদান করুন।';
          break;
        case 'suggest':
          message = 'আপনার পরামর্শ পাঠান, এবং আমরা তা পর্যালোচনা করব।';
          document.getElementById('user-feedback-section').style.display = 'block'; // Show feedback section when "suggest" is clicked
          break;
        case 'location':
          message = 'আপনার পণ্যের অবস্থান জানতে, আমাদের ম্যাপ সিস্টেম ব্যবহার করুন।';
          break;
        default:
          message = 'আপনার প্রশ্নের উত্তর শীঘ্রই দেওয়া হবে।';
          break;
      }
  
      responseBox.innerHTML = message;
      responseBox.style.opacity = 1; // Fade in the response message
    }, 1500); // 1.5 seconds delay for loading
  }
  
  // Function to handle feedback submission
  function sendFeedback() {
    const feedback = document.getElementById('user-feedback').value;
    if (feedback) {
      alert('Feedback submitted: ' + feedback);
      // Add logic to save or send feedback to the server
      document.getElementById('user-feedback').value = ''; // Clear textarea after submission
      document.getElementById('user-feedback-section').style.display = 'none'; // Hide feedback section after submitting
    } else {
      alert('Please provide your feedback.');
    }
  }
  