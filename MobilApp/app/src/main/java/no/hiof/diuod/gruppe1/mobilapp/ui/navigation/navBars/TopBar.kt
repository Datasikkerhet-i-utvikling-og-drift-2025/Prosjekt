package no.hiof.diuod.gruppe1.mobilapp.ui.navigation.navBars

import androidx.compose.material3.Icon
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.MoreVert
import androidx.compose.material3.CenterAlignedTopAppBar
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.IconButton
import androidx.compose.material3.Text
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.runtime.Composable
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.tooling.preview.Preview
import androidx.navigation.NavController
import androidx.navigation.compose.rememberNavController

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun TopBar(navController: NavController, backButtonDestination: String? = null) {

    val currentScreen = getCurrentScreen(navController)
    val isScreenInBottomNavBar = shortcuts.any { it.route.name == currentScreen }

    CenterAlignedTopAppBar(
        title = {
            Text(
                text = "App",
                textAlign = TextAlign.Center
            )
        },

        navigationIcon = {
            if (!isScreenInBottomNavBar) {
                IconButton(onClick = {
                    if (backButtonDestination != null) {
                        navController.navigate(backButtonDestination)
                    } else {
                        navController.popBackStack()
                    }
                }) {
                    Icon(
                        imageVector = Icons.AutoMirrored.Filled.ArrowBack,
                        contentDescription = "Back button"
                    )
                }
            }
        },

        actions = {
            IconButton( onClick = {  } ) {
                Icon(
                    imageVector = Icons.Filled.MoreVert,
                    contentDescription = "Dropwodn menu"
                )
            }
        },

        colors = TopAppBarDefaults.centerAlignedTopAppBarColors(
            containerColor = Color.LightGray
        )


    )
}

@Preview
@Composable
fun TopBarPreview() {
    TopBar(rememberNavController())
}