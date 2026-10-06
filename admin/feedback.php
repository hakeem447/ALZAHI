<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";


/* =========================================================
   GET FEEDBACK
========================================================= */

$feedbacks = [];


$result = $conn->query("
    SELECT
        id,
        order_id,
        customer_name,
        customer_phone,
        rating,
        comment,
        created_at
    FROM feedback
    ORDER BY id DESC
");


if ($result) {

    while (
        $row = $result->fetch_assoc()
    ) {

        $feedbacks[] = $row;

    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Customer Feedback</title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            padding: 30px 20px;

            background: #f5f7f6;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            color: #222;

        }


        .container {

            max-width: 1100px;

            margin: auto;

        }


        .header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

            margin-bottom: 25px;

        }


        h1 {

            margin: 0 0 5px;

        }


        .subtitle {

            margin: 0;

            color: #777;

        }


        .back {

            text-decoration: none;

            background: #e9ecef;

            color: #222;

            padding: 11px 16px;

            border-radius: 9px;

        }


        .feedback-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 18px;

        }


        .feedback-card {

            background: #fff;

            padding: 22px;

            border-radius: 16px;

            box-shadow:
                0 8px 30px
                rgba(0,0,0,.06);

        }


        .top {

            display: flex;

            justify-content: space-between;

            gap: 15px;

            margin-bottom: 15px;

        }


        .customer {

            font-weight: 700;

        }


        .date {

            color: #888;

            font-size: 13px;

            margin-top: 5px;

        }


        .stars {

            font-size: 22px;

            letter-spacing: 2px;

        }


        .comment {

            margin-top: 15px;

            padding: 15px;

            background: #f7f9f8;

            border-radius: 10px;

            line-height: 1.6;

        }


        .order {

            margin-top: 12px;

            font-size: 13px;

            color: #777;

        }


        .phone {

            color: #236b43;

            text-decoration: none;

            font-size: 14px;

        }


        .empty {

            background: #fff;

            padding: 40px;

            border-radius: 15px;

            text-align: center;

            color: #777;

        }


        @media (max-width: 700px) {

            .header {

                flex-direction: column;

                align-items: flex-start;

            }


            .feedback-grid {

                grid-template-columns: 1fr;

            }

        }

    </style>

</head>


<body>


<div class="container">


    <div class="header">

        <div>

            <h1>
                Customer Feedback
            </h1>

            <p class="subtitle">
                Reviews and ratings from customers.
            </p>

        </div>


        <a
            href="dashboard.php"
            class="back"
        >
            Back to Dashboard
        </a>

    </div>


    <?php if (
        empty($feedbacks)
    ): ?>

        <div class="empty">

            No customer feedback yet.

        </div>

    <?php endif; ?>


    <div class="feedback-grid">


        <?php foreach (
            $feedbacks as $feedback
        ): ?>


            <div class="feedback-card">


                <div class="top">

                    <div>

                        <div class="customer">

                            <?= htmlspecialchars(
                                $feedback["customer_name"]
                                ?: "Customer"
                            ) ?>

                        </div>


                        <?php if (
                            !empty(
                                $feedback["customer_phone"]
                            )
                        ): ?>

                            <a
                                class="phone"
                                href="tel:<?= htmlspecialchars(
                                    $feedback["customer_phone"]
                                ) ?>"
                            >
                                <?= htmlspecialchars(
                                    $feedback["customer_phone"]
                                ) ?>
                            </a>

                        <?php endif; ?>


                        <div class="date">

                            <?= htmlspecialchars(
                                $feedback["created_at"]
                            ) ?>

                        </div>

                    </div>


                    <div class="stars">

                        <?php

                        $rating =
                            (int)$feedback["rating"];

                        for (
                            $i = 1;
                            $i <= 5;
                            $i++
                        ):

                        ?>

                            <?= $i <= $rating
                                ? "★"
                                : "☆" ?>

                        <?php endfor; ?>

                    </div>

                </div>


                <?php if (
                    !empty(
                        $feedback["comment"]
                    )
                ): ?>

                    <div class="comment">

                        <?= nl2br(
                            htmlspecialchars(
                                $feedback["comment"]
                            )
                        ) ?>

                    </div>

                <?php endif; ?>


                <div class="order">

                    Order:

                    #<?= (int)$feedback["order_id"] ?>

                </div>


            </div>


        <?php endforeach; ?>


    </div>


</div>


</body>

</html>